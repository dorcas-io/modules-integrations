<div class="modal fade" id="integration-configurations-modal" tabindex="-1" role="dialog" aria-labelledby="integration-configurations-modalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="integration-configurations-modalLabel">@{{ typeof integration.id !== 'undefined' ? 'Edit Configuration' : 'Setup Configuration' }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="#" method="post" id="form-integration_configuration">
          {{ csrf_field() }}
          Configuration for @{{ integration.display_name }}
          <fieldset class="form-fieldset">
            <div class="form-group" v-for="(setting, index) in integration.configurations" :key="integration.name + index">
              <label class="form-label" v-bind:for="integration.name + index">@{{ setting.label }}</label>
              <input class="form-control" v-bind:id="integration.name + index" type="text" v-model="integration.configurations[index].value" required>
            </div>
          </fieldset>
          <input type="hidden" name="integration_id" id="integration_id" v-model="integration.id" v-if="showIntegrationId" />
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button v-on:click.prevent="installIntegration" name="integration_configuration" form="form-integration_configuration" :data-index="integration_index" class="btn btn-primary">@{{ typeof integration.id !== 'undefined' ? 'Update Integration' : 'Install Integration' }}</button>
      </div>
    </div>
  </div>
</div>