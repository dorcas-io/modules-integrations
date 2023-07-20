@extends('layouts.tabler')
@section('body_content_header_extras')

@endsection
@section('body_content_main')
@include('layouts.blocks.tabler.alert')

<div class="row">
    @include('layouts.blocks.tabler.sub-menu')

    <div class="col-md-9 col-xl-9">
	    <ul class="nav nav-tabs nav-justified">
	        <li class="nav-item">
	            <a class="nav-link active" data-toggle="tab" href="#integrations-my">My Integrations</a>
	        </li>
	        <li class="nav-item">
	            <a class="nav-link" data-toggle="tab" href="#integrations-all">Integrations</a>
	        </li>
	    </ul>

	    <div class="tab-content" id="listing_integrations">
	        <div class="tab-pane container active" id="integrations-my">
	            <br/>
	            
		        <div class="container" id="listing_myintegrations">
		            <div class="row mt-3" v-if="integrations_my.length > 0">

                         <div class="card card-aside" v-for="(integration, index) in integrations_my" :key="integration.id" :integration="integration" :index="index">
                         	<a href="#" class="card-aside-column" v-bind:style="{ 'background-image': 'url(' + integration.image_url + ')' }"></a>
                         	<div class="card-body d-flex flex-column">
                         		<h4>@{{ integration.display_name }}</h4>
                         		<div class="text-muted">@{{ integration.description }}</div>
                         		<div class="d-flex align-items-center pt-5 mt-auto">
											{{-- dd(config('dorcas')['integrations'][2]['configurations'][0]['token_value'])  --}}
											@php 
											  $token = config('dorcas')['integrations'][2]['configurations'][0]['token_value'] ?? null;
											@endphp
                         			<div>
                         				<a v-if="integration.type_integration=='keys'" href="#" v-on:click.prevent="configureIntegration" data-list="my" :data-index="index" :data-display-name="integration.display_name" :data-name="integration.name" :data-display-name="integration.display_name" :data-type="integration.type" :data-type-integration="integration.type_integration" class="btn btn-sm btn-outline-primary">Configuration</a>
                         				<a v-if="integration.type_integration=='oauth2' && authRequirements(index)=='token_absent'" v-on:click.prevent="authenticateIntegration" :data-index="index" :data-display-name="integration.display_name" :data-name="integration.name" :data-display-name="integration.display_name" :data-type="integration.type" :data-type-integration="integration.type_integration" href="#" class="btn btn-sm btn-outline-primary">Authenticate</a>
												 <a v-if="integration.type_integration=='app_token'" target="_blank" href="{{ env('EXTERNAL_LINK').'?app_token='.$token}}" :data-index="index" :data-display-name="integration.display_name" :data-name="integration.name" :data-display-name="integration.display_name" :data-type="integration.type" :data-type-integration="integration.type_integration" href="#" class="btn btn-sm btn-outline-primary">Authenticate</a>
                         				<small v-if="integration.type_integration=='oauth2' && authRequirements(index)=='token_absent'" class="d-block text-muted">Click <strong>Authenticate</strong> to grant Hub permission from Hubspot</small>
                         				<small v-if="integration.type_integration=='oauth2' && authRequirements(index)=='token_present'" class="d-block text-muted">Installed and <strong>Authenticated</strong></small>
                         			</div>
                         			<div class="ml-auto text-muted">
                         				<a href="#" v-on:click.prevent="uninstallIntegration" :data-id="integration.id" :data-display-name="integration.display_name" :data-index="index" :data-id="integration.id" class="btn btn-sm btn-outline-danger">Uninstall</a>
                         			</div>
                         		</div>
                         	</div>
                         </div>

		            </div>
		            <div class="col s12" v-if="integrations_my.length === 0">
		                @component('layouts.blocks.tabler.empty-card')
		                    You have no Integrations installed
		                    @slot('buttons')
		                        <a href="#" v-on:click.prevent="createDepartment" class="btn btn-primary btn-sm">All Integrations</a>
		                    @endslot
		                @endcomponent
		            </div>
		        </div>


	        </div>
	        <div class="tab-pane container" id="integrations-all">
	            <br/>
				<div class="row">
					<div class="col s12">
						@component('layouts.blocks.tabler.alert-with-buttons')
							@slot('title')
								 Configuration Integration  Guide
							@endslot
						  <h5>Paytsack Integration Guide</h5>
							<ul>
								<li>Goto Paystack dashboard , copy live secret key and live public key</li>
								<li>Click on configure Integration to add the public and secret live keys you copied from paystack</li>
							</ul>

								<h5>Flutterwave Integration Guide</h5>
								<ul>
									<li>Goto Flutterwave  dashboard , copy live secret key and live public key</li>
									<li>Click on configure Integration to add the public and secret live keys you copied from flutterwave</li>
								</ul>
							@slot('buttons')
{{--								<button v-on:click.prevent="resendVerification" class="btn btn-secondary" :class="{'btn-loading':verifying}" type="button">Send Verification Email</button>--}}
							@endslot
						@endcomponent
					</div>

				</div>
				<br>
		        <div class="container" id="listing_allintegrations">
		            <div class="row mt-3" v-if="!emptyAllIntegrations">

                         <div class="card card-aside" v-for="(integration, index) in integrations_all" :integration="integration" :index="index">
                         	<a href="#" class="card-aside-column" v-bind:style="{ 'background-image': 'url(' + integration.image_url + ')' }"></a>
                         	<div class="card-body d-flex flex-column">
                         		<h4>@{{ integration.display_name }}</h4>
                         		<div class="text-muted">@{{ integration.description }}</div>
                         		<div class="d-flex align-items-center pt-5 mt-auto">
                         			<div>
                         				<!-- <a href="" class="text-default"></a> -->
                         				<!-- <small class="d-block text-muted">3 days ago</small> -->
                         			</div>
                         			<div class="ml-auto text-muted">
                         				<a href="#" v-if="integration.type_integration=='keys' || integration.type_integration=='app_token'" v-on:click.prevent="configureIntegration" data-list="all" :data-index="index" :data-display-name="integration.display_name" :data-name="integration.name" :data-display-name="integration.display_name" :data-type="integration.type" :data-type-integration="integration.type_integration" class="btn btn-sm btn-outline-success" v-html="installLabel(integration.type_integration)"></a>
                         				<a href="#" v-if="integration.type_integration=='oauth2' && !installing" v-on:click.prevent="installIntegration" data-list="all" :data-index="index" :data-display-name="integration.display_name" :data-name="integration.name" :data-display-name="integration.display_name" :data-type="integration.type" :data-type-integration="integration.type_integration" class="btn btn-sm btn-outline-success" v-html="installLabel(integration.type_integration)"></a>
                         			</div>
                         		</div>
                         	</div>
                         </div>

		            </div>

		            <div class="col s12" v-if="integrations_all.length === 0">
		                @component('layouts.blocks.tabler.empty-card')
		                    It appears you have installed all Integrations
		                    @slot('buttons')
		                    @endslot
		                @endcomponent
		            </div>

		        </div>
	        </div>
	        @include('modules-integrations::modals.configurations')
	    </div>

    </div>

</div>


@endsection
@section('body_js')
    <script type="text/javascript">
        let iVue = new Vue({
            el: '#listing_integrations',
            data: {
                integrations_my: {!! !empty($integrations) ? json_encode($integrations) : '[]' !!},
                integrations_all: {!! !empty($availableIntegrations) ? json_encode($availableIntegrations) : '{}' !!},
                integration_my: {name: '', type: '', configurations: []},
                integration_all: {name: '', type: '', configurations: []},
                integration: {name: '', type: '', configurations: []},
                integration_index: 0,
                integration_list: 'all',
                uiresponse: {!! !empty($UiResponse) ? json_encode($UiResponse) : '{}' !!},
                installing: false,
                installed: {!! !empty($installed) ? json_encode($installed) : '{}' !!}
            },
            mounted: function () {
            	//console.log(this.integrations_my);
            	//console.log(this.integrations_all);
            	//console.log(this.installed);
                //this.searchAppStore();
                //console.log(this.integrations_my.length)
                //console.log(Object.keys(this.integrations_all).length)
            },
            computed: {
                emptyAllIntegrations: function () {
                	if (typeof(this.integrations_all)==="object") {
                		return Object.keys(this.integrations_all).length === 0;
                	} else {
                		return this.integrations_all.length === 0;
                	}
                },
                showIntegrationId: function () {
                    return typeof this.integration.id !== 'undefined';
                }
            },
            methods: {
                installLabel: function (integration_type) {
                	if (integration_type=="keys") {
                		return 'Configure Integration'
                	} else if (integration_type=="oauth2") {
                		return 'Install Integration'
                	} 
                    return 'Install'
                },
                authRequirements: function (index) {
                	var requirements = 'token_absent';
                	let integration = typeof this.integrations_my[index] !== 'undefined' ? this.integrations_my[index] : null;
            		if (integration === null) {
            			return {};
            		}
                	let oauth_refresh_token = integration.configurations.find( conf => conf.name=="oauth_refresh_token" )
                	if (oauth_refresh_token !== null && oauth_refresh_token.value !==null ) {
            			requirements = 'token_present'
            		}
            		//console.log(requirements)
            		return requirements;
                },
            	removeIntegration: function (index) {
                    console.log('Removing Index: ' + index);
                    this.integrations.splice(index, 1);
                },
                is_installed: function (index) {
                    this.integrations.splice(index, 1);
                },

                spliceAllIntegrations: function(dindex) {
                	if (typeof(this.integrations_all)==="object") {
                		
                	} else {
                		this.integrations_all.splice(dindex, 1);
                	}
                },

                authenticateIntegration: function ($event) {
                		let index = $event.target.getAttribute('data-index');
                		let integration = typeof this.integrations_my[index] !== 'undefined' ? this.integrations_my[index] : null;
                		if (integration === null) {
                			return;
                		}
                		let auth_url = integration.configurations.find( conf => conf.name==="oauth_url" )
                		//console.log(auth_url.value)
                		window.open(auth_url.value)
                },
					 

                configureIntegration: function ($event) {
                		let index = $event.target.getAttribute('data-index');
                		let list = $event.target.getAttribute('data-list');
                		this.integration_list = list;
                		var integration;
                		if (list=="my") {
                			integration = typeof this.integrations_my[index] !== 'undefined' ? this.integrations_my[index] : null;
                		} else {
                			integration = typeof this.integrations_all[index] !== 'undefined' ? this.integrations_all[index] : null;
                		}
                		if (integration === null) {
                			return;
                		}
                		this.integration = integration;
                		this.integration_index = index;
                		$('#integration-configurations-modal').modal('show');
                },

		        installIntegration: function ($event) {
		            var context = this;
		            //context.installing = true;
                	let index = $event.target.getAttribute('data-index');
                	var integration, act, action;
            		if (this.integration_list=="my") {
            			integration = typeof this.integrations_my[index] !== 'undefined' ? this.integrations_my[index] : null;
            			act = "update"
            			action = "Updated"
            		} else {
            			integration = typeof this.integrations_all[index] !== 'undefined' ? this.integrations_all[index] : null;
            			act = "install"
            			action = "Installed"
            		}
            		if (integration === null) {
            			return;
            		}

		            let display_name = integration.display_name;
					let integration_id = context.showIntegrationId ? integration.id : null;
		            let integration_name = integration.name;
		            let integration_type = integration.type;
		            let integration_configurations = integration.configurations;
		            //console.log(integration_name)
		            //console.log(integration_type)
		            //console.log(integration_configurations)
		            Swal.fire({
		                title: "Are you sure?",
		                text: "You are about to " + act + " the " + display_name + " integration.",
		                type: "info",
		                showCancelButton: true,
		                confirmButtonText: "Yes, " + act +" it!",
		                showLoaderOnConfirm: true,
		                preConfirm: (install_integration) => {
		                	this.installing = true;
				            return axios.post("/mit/integrations", {
								integration_id: integration_id,
				                type: integration_type,
				                name: integration_name,
				                configurations: integration_configurations
				            }).then(function (response) {
				                console.log(response);
				                //context.installed = true;
				                //context.installing = false;
				                //context.$emit('is_installed', context.index);
				                //Materialize.toast('Added the ' + context.display_name + ' integration to your account.', 4000);
		                        $('#integration-configurations-modal').modal('hide');
		                        context.spliceAllIntegrations(index)
		                        window.location = '{{ url()->current() }}';
				                return swal(action, action + ' the ' + display_name + ' integration to your account.', "success");
				            }).catch(function (error) {
				            	this.installing = false;
				                var message = '';
				                if (error.response) {
				                    // The request was made and the server responded with a status code
				                    // that falls out of the range of 2xx
				                    //var e = error.response.data.errors[0];
				                    //message = e.title;
		                            var e = error.response;
		                            message = e.data.message;
				                } else if (error.request) {
				                    // The request was made but no response was received
				                    // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
				                    // http.ClientRequest in node.js
				                    message = 'The request was made but no response was received';
				                } else {
				                    // Something happened in setting up the request that triggered an Error
				                    message = error.message;
				                }
				                context.installing = false;
				                //Materialize.toast("Oops!" + message, 4000);
				                return swal("Install Failed", message, "warning");
				            });



		                },
		                allowOutsideClick: () => !Swal.isLoading()
		            });
		        },

		        uninstallIntegration: function ($event) {
		            var context = this;
		            let target = $event.target;
		            //console.log(target);
		            let name = target.getAttribute('data-display-name');
		            let id = target.getAttribute('data-id');
		            let index = target.getAttribute('data-index');
		            //console.log(target.getAttribute('data-configurations'));
		            Swal.fire({
		                title: "Are you sure?",
		                text: "You are about to uninstall the " + name + " integration.",
		                type: "warning",
		                showCancelButton: true,
		                confirmButtonText: "Yes, uninstall it!",
		                showLoaderOnConfirm: true,
		                preConfirm: (uninstall_integration) => {
		                context.updating = true;
		                return axios.delete("/mit/integrations/" + id)
		                    .then(function (response) {
		                        console.log(response);
		                        //context.visible = false;
		                        console.log('Removing Index: ' + index);
		                        context.integrations_my.splice(index, 1);
		                        //context.$emit('remove-integration', context.index);
		                        window.location = '{{ url()->current() }}';
		                        return swal("Uninstalled!", "The integration was successfully uninstalled.", "success");
		                    })
		                    .catch(function (error) {
		                        var message = '';
		                        if (error.response) {
		                            // The request was made and the server responded with a status code
		                            // that falls out of the range of 2xx
		                            //var e = error.response.data.errors[0];
		                            //message = e.title;
		                            var e = error.response;
		                            message = e.data.message;
		                        } else if (error.request) {
		                            // The request was made but no response was received
		                            // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
		                            // http.ClientRequest in node.js
		                            message = 'The request was made but no response was received';
		                        } else {
		                            // Something happened in setting up the request that triggered an Error
		                            message = error.message;
		                        }
		                        return swal("Uninstall Failed", message, "warning");
		                    });
		                },
		                allowOutsideClick: () => !Swal.isLoading()
		            });

		        },

                updateApp: function () {
		            var context = this;
		            context.updating = true;
		            axios.put("/xhr/integrations/" + context.id, {
		                type: context.integration_type,
		                name: context.integration_name,
		                configurations: context.integration_configurations
		            }).then(function (response) {
		                console.log(response);
		                context.updating = false;
		                //Materialize.toast('Updated the settings for the ' + context.display_name + ' integration.', 4000);
		            }).catch(function (error) {
		                var message = '';
		                if (error.response) {
		                    // The request was made and the server responded with a status code
		                    // that falls out of the range of 2xx
		                    //var e = error.response.data.errors[0];
		                    //message = e.title;
		                    var e = error.response;
		                    message = e.data.message;
		                } else if (error.request) {
		                    // The request was made but no response was received
		                    // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
		                    // http.ClientRequest in node.js
		                    message = 'The request was made but no response was received';
		                } else {
		                    // Something happened in setting up the request that triggered an Error
		                    message = error.message;
		                }
		                context.updating = false;
		                Materialize.toast("Oops!" + message, 4000);
		            });
		        }


            }
        });
    </script>
@endsection