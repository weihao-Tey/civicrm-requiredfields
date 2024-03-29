{* HEADER *}

<!--

{* FIELD EXAMPLE: OPTION 1 (AUTOMATIC LAYOUT) *}

{foreach from=$elementNames item=elementName}
  <div class="crm-section">
    <div class="label">{$form.$elementName.label}</div>
    <div class="content">{$form.$elementName.html}</div>
    <div class="clear"></div>
  </div>
{/foreach}

-->

{* FIELD EXAMPLE: OPTION 2 (MANUAL LAYOUT) *}

<div class="crm-section">
  {* Contact ID field *}
  <div class="label">{$form.contact_id.label}</div>
  <div class="content">{$form.contact_id.html}</div>
  <div class="clear"></div> 
</div>

{* Required Activity ID field *}
<div class="crm-section">
  <div class="label">{$form.required_activity_id.label}</div>
  <div class="content">{$form.required_activity_id.html}</div>
  <div class="clear"></div>
</div>

{* Relationship Type ID field *}
<div class="crm-section">
  <div class="label">{$form.relationship_type_id.label}</div>
  <div class="content">{$form.relationship_type_id.html}</div>
  <div class="clear"></div>
</div>


{* FOOTER *}
<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
