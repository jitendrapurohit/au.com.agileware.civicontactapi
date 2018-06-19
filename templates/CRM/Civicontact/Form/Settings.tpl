{if $profile_warning}
    <div class="messages status no-popup">
        <p>{$profile_warning}</p>

        <table class="display">
            <thead>
                <th>Field Type</th>
                <th>Field Name</th>
            </thead>
            <tbody>
                {foreach from=$notsupported_profile_fields item=notsupported_profile_field}
                    <tr>
                        <td>{$notsupported_profile_field.field_type}</td>
                        <td>{$notsupported_profile_field.label}</td>
                    </tr>
                {/foreach}
            </tbody>
        </table><br>

        Create new profile or copy existing to use in CiviContact.
    </div>
{/if}

<div class="crm-block crm-form-block crm-case-form-block">

<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="top"}
</div>

<table class="form-layout">
{foreach from=$elementNames item=element}
  {assign var="elementName" value=$element.name}
  {if ($elementName != 'cca_licence_activated')}
      <tr>
          <td class="label">{$form.$elementName.label}</td>
          <td>{$form.$elementName.html}<br />
            <span class="description">{ $element.description }</span>
              {if $elementName == 'cca_licence_code' and $form.$elementName.value != '' and !$licenceActivated}<br><br>
                  <span class="description">Please click submit to update existing licence code before activating code.<br></span>
                  <a href="{crmURL p='civicrm/cca/activatelicence'}">Activate Licence</a><br><br>
              {/if}
          </td>
      </tr>
  {/if}
{/foreach}
</table>

<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>

</div>