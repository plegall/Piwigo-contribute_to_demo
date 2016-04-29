{combine_script id='ctd_picture_modify' load='footer' require='jquery' path='plugins/contribute_to_demo/picture_modify.js'}

{html_style}
.contrib .loading { display:none }

.ctd_submit { display:{if empty($CTD_UUID)}inline{else}none{/if} }
.ctd_pending { display: {if $CTD_STATE eq 'submitted'}inline{else}none{/if} }
.ctd_remove { display: {if $CTD_STATE eq 'validated' or $CTD_STATE eq 'submitted'}inline{else}none{/if} }
.ctd_see { display: {if $CTD_STATE eq 'validated'}inline{else}none{/if} }
{/html_style}

{strip}
<li class="contrib"
  data-demo_url="{$CTD_DEMO_URL}"
  data-id="{$CTD_ID}"
  data-uuid="{$CTD_UUID}"
  data-width="{$CTD_WIDTH}"
  data-height="{$CTD_HEIGHT}"
  >
  <span class="ctd_submit"><a class="icon-upload" href="#">{'Contribute to demo'|@translate}</a></span>
  <span class="ctd_pending">{'pending in demo'|@translate}</span>
  <span class="ctd_see"><a class="icon-ok-circled" href="" target="_blank">{'See in demo'|@translate}</a></span>
  <span class="ctd_remove"><a class="icon-cancel-circled" href="#">{'Remove from demo'|@translate}</a></span>
  <img class="loading" src="themes/default/images/ajax-loader-small.gif">
</li>
{/strip}
