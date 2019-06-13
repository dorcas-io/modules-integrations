<?php

namespace Dorcas\ModulesIntegrations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Dorcas\ModulesIntegrations\Models\ModulesIntegrations;
use App\Dorcas\Hub\Utilities\UiResponse\UiResponse;
use App\Http\Controllers\HomeController;
use Hostville\Dorcas\Sdk;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Exceptions\RecordNotFoundException;

class ModulesIntegrationsController extends Controller {

    public function __construct()
    {
        parent::__construct();
        $this->data = [
            'page' => ['title' => config('modules-integrations.title')],
            'header' => ['title' => config('modules-integrations.title')],
            'selectedMenu' => 'addons',
            'submenuConfig' => 'navigation-menu.modules-integrations.sub-menu',
            'submenuAction' => ''
        ];
    }

    public function index(Request $request, Sdk $sdk)
    {
        $availableIntegrations = config('dorcas.integrations');
        # get all the available integrations
        $integrations = collect([]);
        # the installed integrations
        $installed = $this->getIntegrations($sdk);
        /*$installedNames = [];
        if (!empty($installed)) {
            $installedNames = $installed->pluck('name')->all();
        }*/
        $installedNames = !empty($installed) && $installed->count() > 0 ? $installed->pluck('name')->all() : [];
        foreach ($availableIntegrations as $index => $integration) {
            if (($installedIndex = array_search($integration['name'], $installedNames, true)) === false) {
                continue;
            }
            $installedIntegration = $installed->get($installedIndex);
            $integration['id'] = $installedIntegration->id;
            $integration['configurations'] = $installedIntegration->configuration;
            # update the values
            $integrations->push($integration);
            # add the integration

            unset($availableIntegrations[$index]);

        }
        $this->data['integrations'] = $integrations;
        $this->data['availableIntegrations'] = $availableIntegrations;
        $this->data['installed'] = $installed;

        //dd(array($integrations,$availableIntegrations));
        $this->setViewUiResponse($request);

    	return view('modules-integrations::index', $this->data);
    }

    /**
     * @param Request $request
     * @param Sdk     $sdk
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function install(Request $request, Sdk $sdk)
    {
        $this->validate($request, [
            'type' => 'required|string|max:30',
            'name' => 'required|string|max:50',
            'configurations' => 'nullable|array',
        ]);

        # validate the request
        $configurations = $request->has('configurations') ? $request->configurations : [];
        # set the configurations

        $integrationId = $request->has('integration_id') ? $request->input('integration_id') : null;

        $resource = $sdk->createIntegrationResource($integrationId)->addBodyParam('type', $request->input('type'))
                                                    ->addBodyParam('name', $request->input('name'))
                                                    ->addBodyParam('configuration', $configurations);
        $query = $resource->send(empty($integrationId) ? 'post' : 'put');
        # send request
        if (!$query->isSuccessful()) {
            $message = $query->getErrors()[0]['title'] ?? 'Failed while trying to '. (empty($integrationId) ? 'install' : 'update') .' the Integration.';
            throw new \RuntimeException($message);
        }
        $company = $request->user()->company(true, true);
        Cache::forget('integrations.'.$company->id);
        return response()->json($query->getData());
    }

    /**
     * @param Request $request
     * @param Sdk     $sdk
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /*public function update(Request $request, Sdk $sdk, string $id)
    {
        $this->validate($request, [
            'type' => 'required|string|max:30',
            'name' => 'required|string|max:50',
            'configurations' => 'nullable|array',
        ]);
        # validate the request
        $configurations = $request->has('configurations') ? $request->configurations : [];
        # set the configurations
        $query = $sdk->createIntegrationResource($id)->addBodyParam('type', $request->input('type'))
                                                        ->addBodyParam('name', $request->input('name'))
                                                        ->addBodyParam('configuration', $configurations)
                                                        ->send('put');
        # send request
        if (!$query->isSuccessful()) {
            $message = $query->getErrors()[0]['title'] ?? 'Failed while trying to update the app integration.';
            throw new \RuntimeException($message);
        }
        $company = $request->user()->company(true, true);
        Cache::forget('integrations.'.$company->id);
        return response()->json($query->getData());
    }*/


    /**
     * @param Request $request
     * @param Sdk     $sdk
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function uninstall(Request $request, Sdk $sdk, string $id)
    {
        $model = $sdk->createIntegrationResource($id);
        $response = $model->send('delete');
        # make the request
        if (!$response->isSuccessful()) {
            // do something here
            $message = $response->getErrors()[0]['title'] ?? 'Failed while uninstalling the integration.';
            throw new RecordNotFoundException($message);
        }
        $company = $request->user()->company(true, true);
        Cache::forget('integrations.'.$company->id);
        $this->data = $response->getData();
        return response()->json($this->data);
    }


    /**
     * @param Request $request
     * @param Sdk     $sdk
     * @param String     $key
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function oauth_callback(Request $request, Sdk $sdk, $key)
    {
        $integrations = config('dorcas.integrations');
        $availableCallbackKeys = [];
        foreach ($integrations as $ikey => $ivalue) {
            if ($ivalue["type_integration"]=="oauth2") {
                $config = 'modules-integrations.config.'. $ivalue["name"];
                $integration = config($config);
                $availableCallbackKeys[$integration["oauth_callback_key"]] = $ivalue["name"];
            }
        }

        //dd(array($availableCallbackKeys, $key));

        if (!empty($key)) {
            if (array_key_exists(trim($key), $availableCallbackKeys)) {
                $c = $availableCallbackKeys[$key];
                $configuration = config('modules-integrations.config.'. $c);
                $code = $configuration["oauth_callback_param"];
                $callback_code = $request->has($code) ? $request->{$code} : "";

                $configuration["oauth_token_params"]["code"] = $callback_code;

                $params = [
                    "url" => $configuration["oauth_token_url"],
                    "params" => $configuration["oauth_token_params"],
                    "method" => $configuration["oauth_token_method"],
                    "headers" => $configuration["oauth_token_headers"],
                ];

                $callback_response = $this->oauth_curl($params);

                $bad_statuses = array("BAD_AUTH_CODE");

                if ($callback_response["type"]=="success" && !empty($callback_response["success"]["refresh_token"])) {
                    //store refresh token and portal ID
                        try {

                            $access_token = $callback_response["success"]["access_token"];
                            $portal_params = [
                                "url" => $configuration["portal_url"],
                                "params" => [],
                                "method" => $configuration["portal_method"],
                                "headers" => ["Authorization: Bearer ".$access_token],
                            ];
                            $portal_response = $this->oauth_curl($portal_params);
                            $response_param = $configuration["portal_response_param"];

                            //$portalid = empty($portal_response["error"]) ? $portal_response["success"][$response_param] : "";
                            $portalid = $portal_response["success"][$response_param];


                            $installed = $this->getIntegrations($sdk);
                            $installedNames = !empty($installed) && $installed->count() > 0 ? $installed->pluck('name')->all() : [];
                            if ( ($installedIndex = array_search($c, $installedNames, true)) !== false) {
                                $installedIntegration = $installed->get($installedIndex);
                                $integrationId = $installedIntegration->id;
                                $integrationConfig = $installedIntegration->configuration;
                                //dd($integrationConfig);
                                foreach ($integrationConfig as $ckey => $cvalue) {
                                    if ($cvalue["name"]=="oauth_refresh_token") {
                                        $integrationConfig[$ckey]["value"] = $callback_response["success"]["refresh_token"];
                                    }
                                    if ($cvalue["name"]=="portal_id" && !empty($portalid)) {
                                        $integrationConfig[$ckey]["value"] = $portalid;
                                    }
                                }
                                $resource = $sdk->createIntegrationResource($integrationId)->addBodyParam('configuration', $integrationConfig)->send('put');
                                if (!$resource->isSuccessful()) {
                                    $message = $query->getErrors()[0]['title'] ?? 'Failed while trying to update the '.$configuration["display_name"].' Integration.';
                                    throw new \RuntimeException($message);
                                }
                                $company = $request->user()->company(true, true);
                                Cache::forget('integrations.'.$company->id);
                                $response = (tabler_ui_html_response(['Successfully authenticated the '.$configuration["display_name"].' integration.']))->setType(UiResponse::TYPE_SUCCESS);
                            }
                        } catch (\Exception $e) {
                            $response = (tabler_ui_html_response([$e->getMessage()]))->setType(UiResponse::TYPE_ERROR);
                        }

                } elseif ($callback_response["type"]=="success" && !empty($callback_response["success"]["status"]) && in_array($callback_response["success"]["status"], $bad_statuses) ) {
                    $response = (tabler_ui_html_response(['Problem authenticating the '.$configuration["display_name"].' integration.']))->setType(UiResponse::TYPE_ERROR);
                }

                //dd($callback_response);

                //$response = $response ?: (tabler_ui_html_response(['Unknown Issue authenticating the '.$configuration["display_name"].' integration.']))->setType(UiResponse::TYPE_ERROR);

                return redirect(route('integrations-main'))->with('UiResponse', $response);

            }
        }

    }


    private function oauth_curl($params) {

        //dd($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $params["url"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if ($params["method"]=="POST") {
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params["params"]));
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $params["headers"]);
        $data = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        $response = ["type" => "", "success" => "", "error" => ""];
 
        if (!$err) {
            $response["type"] = "success";
            $response["success"] = json_decode($data, true); 
        } else {
            $response["type"] = "error";
            $response["error"] = $err;
        }

        return $response;
        //return $params;
    }


    public function webhooks(Request $request, Sdk $sdk) {

       //parse request variables & integration name
        $portal_id = $request->portal_id ?? 0;
        $integration_name = $request->integration_name ?? ""; //can be gotten from http referrer or poster or. header info

        //?? Save list of integrations into user configurations

       /* if (!empty($portal_id) && !empty($integration_name)) {
            //get user account via portal ID
            $installed = $this->getIntegrations($sdk);
            $installedNames = !empty($installed) && $installed->count() > 0 ? $installed->pluck('name')->all() : [];
            if ( ($installedIndex = array_search($c, $installedNames, true)) !== false) {
                $installedIntegration = $installed->get($installedIndex);
                $integrationId = $installedIntegration->id;
                $integrationConfig = $installedIntegration->configuration;
                //dd($integrationConfig);
                foreach ($integrationConfig as $ckey => $cvalue) {
                    if ($cvalue["name"]=="oauth_refresh_token") {
                        $integrationConfig[$ckey]["value"] = $callback_response["success"]["refresh_token"];
                    }
                    if ($cvalue["name"]=="portal_id" && !empty($portalid)) {
                        $integrationConfig[$ckey]["value"] = $portalid;
                    }
                }
                $resource = $sdk->createIntegrationResource($integrationId)->addBodyParam('configuration', $integrationConfig)->send('put');
                if (!$resource->isSuccessful()) {
                    $message = $query->getErrors()[0]['title'] ?? 'Failed while trying to update the '.$configuration["display_name"].' Integration.';
                    throw new \RuntimeException($message);
                }
                $company = $request->user()->company(true, true);
                Cache::forget('integrations.'.$company->id);
                $response = (tabler_ui_html_response(['Successfully authenticated the '.$configuration["display_name"].' integration.']))->setType(UiResponse::TYPE_SUCCESS);
            }


        }*/
        


        //process request.

    }


}