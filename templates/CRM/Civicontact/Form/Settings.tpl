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
  <tr>
      <td class="label">{$form.$elementName.label}</td>
      <td>{$form.$elementName.html}<br />
          <span class="description">{ $element.description }</span>
      </td>
  </tr>
{/foreach}
    <tr>
        <td class="label"><label for="cca_reset_qr_code">Reset QR Code</label></td>
        <td><input id="cca_reset_qr_code" name="cca_reset_qr_code" type="checkbox" value="1" class="crm-form-checkbox"><br>
            <span class="description">Re-generate QR codes for all users.</span>
        </td>
    </tr>
    <tr>
        <td class="label"><label for="cca_invalidate_all">Drop Authentication</label></td>
        <td><input id="cca_invalidate_all" name="cca_invalidate_all" type="checkbox" value="1" class="crm-form-checkbox"><br>
            <span class="description">Immediately invalidate all existing user CiviContact authentication, requiring all users to re-authenticate.</span>
        </td>
    </tr>
</table>

<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>

</div>