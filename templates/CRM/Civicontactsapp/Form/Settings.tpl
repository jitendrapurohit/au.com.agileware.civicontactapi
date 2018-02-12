<div class="crm-block crm-form-block crm-case-form-block">

<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="top"}
</div>

<table class="form-layout">
{foreach from=$elementNames item=elementName}
  <tr>
      <td class="label">{$form.$elementName.label}</td>
      <td>{$form.$elementName.html}<br />
        <span class="description">{ $form.$elementName.description }</span>
      </td>
  </tr>

{/foreach}
</table>

<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>

</div>