    <div class="crm-block crm-form-block crm-case-form-block">

<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="top"}
</div>

<table class="form-layout">
{foreach from=$elementNames item=element}
  {assign var="elementName" value=$element.name}
  {if $elementName != 'cca_licence_activated'}
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