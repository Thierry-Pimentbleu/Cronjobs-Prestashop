{* pb_cronjobs — Admin configure template *}

{* ── Flash messages ─────────────────────────────────────────────────────── *}
{foreach $pb_errors as $err}
<div class="alert alert-danger">{$err nofilter}</div>
{/foreach}
{foreach $pb_successes as $msg}
<div class="alert alert-success">{$msg nofilter}</div>
{/foreach}

{* ── Info panel : URL to set in the host cron manager ───────────────────── *}
<div class="panel">
    <div class="panel-heading">
        <i class="icon-clock-o"></i> {$pb_l.cron_task_manager|escape:'html'}
    </div>
    <div class="panel-body">
        <p>{$pb_l.call_url|escape:'html'}</p>
        <div class="input-group">
            <input type="text" id="pb-cron-url" class="form-control" value="{$pb_cron_url|escape:'html'}" readonly />
            <span class="input-group-btn">
                <button class="btn btn-default" type="button" id="pb-copy-url">
                    <i class="icon-copy"></i> {$pb_l.copy|escape:'html'}
                </button>
            </span>
        </div>
        <p class="help-block">{$pb_l.example_curl|escape:'html'} <code>curl -s "{$pb_cron_url|escape:'html'}"</code></p>
    </div>
</div>

{* ════════════════════════════════════════════════════════════════════════════
   MODE : FORM (add / edit)
   ════════════════════════════════════════════════════════════════════════════ *}
{if $pb_mode == 'form'}

<div class="panel">
    <div class="panel-heading">
        <i class="icon-{if $pb_cron}pencil{else}plus{/if}"></i>
        {if $pb_cron}{$pb_l.edit_task|escape:'html'}{else}{$pb_l.new_task|escape:'html'}{/if}
    </div>
    <form action="{$pb_base_link|escape:'html'}{if $pb_cron}&id_pb_cronjob={$pb_cron.id_pb_cronjob}{/if}"
          method="post" class="form-horizontal" id="pb-cron-form">

        {* Description *}
        <div class="form-group">
            <label class="control-label col-lg-3">{$pb_l.lbl_description|escape:'html'} <span class="required">*</span></label>
            <div class="col-lg-9">
                <input type="text" name="description" class="form-control"
                       value="{if $pb_cron}{$pb_cron.description|escape:'html'}{else}{Tools::getValue('description')|escape:'html'}{/if}"
                       placeholder="{$pb_l.placeholder_desc|escape:'html'}" />
            </div>
        </div>

        {* URL *}
        <div class="form-group">
            <label class="control-label col-lg-3">{$pb_l.lbl_url|escape:'html'} <span class="required">*</span></label>
            <div class="col-lg-9">
                <input type="url" name="task" class="form-control"
                       value="{if $pb_cron}{$pb_cron.task|escape:'html'}{else}{Tools::getValue('task')|escape:'html'}{/if}"
                       placeholder="https://www.myshop.com/module/..." />
                <p class="help-block">{$pb_l.url_help|escape:'html'}</p>
            </div>
        </div>

        {* Schedule *}
        <div class="form-group">
            <label class="control-label col-lg-3">{$pb_l.lbl_schedule|escape:'html'}</label>
            <div class="col-lg-9">
                <div class="row">
                    {foreach $pb_fields as $field}
                    <div class="col-xs-6 col-sm-4 col-md-2" style="margin-bottom:8px;">
                        <label class="small text-muted">{$field.label|escape:'html'}</label>
                        <select name="{$field.name}" class="form-control input-sm">
                            {foreach $field.opts as $opt}
                            <option value="{$opt.id}" {if $opt.id == $field.current}selected="selected"{/if}>{$opt.label|escape:'html'}</option>
                            {/foreach}
                        </select>
                    </div>
                    {/foreach}
                </div>
                <p class="help-block">{$pb_l.schedule_help|escape:'html'}</p>
            </div>
        </div>

        {* One shot *}
        <div class="form-group">
            <label class="control-label col-lg-3">{$pb_l.lbl_one_shot|escape:'html'}</label>
            <div class="col-lg-9">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="one_shot" value="1"
                               {if $pb_cron && $pb_cron.one_shot}checked{/if} />
                        {$pb_l.one_shot_help|escape:'html'}
                    </label>
                </div>
            </div>
        </div>

        {* Active *}
        <div class="form-group">
            <label class="control-label col-lg-3">{$pb_l.lbl_active|escape:'html'}</label>
            <div class="col-lg-9">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="active" value="1"
                               {if !$pb_cron || $pb_cron.active}checked{/if} />
                        {$pb_l.active_help|escape:'html'}
                    </label>
                </div>
            </div>
        </div>

        {* No log *}
        <div class="form-group">
            <label class="control-label col-lg-3">{$pb_l.lbl_no_log|escape:'html'}</label>
            <div class="col-lg-9">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="no_log" value="1"
                               {if $pb_cron && $pb_cron.no_log}checked{/if} />
                        {$pb_l.no_log_help|escape:'html'}
                    </label>
                </div>
            </div>
        </div>

        <div class="panel-footer">
            <a href="{$pb_base_link|escape:'html'}" class="btn btn-default">
                <i class="icon-times"></i> {$pb_l.btn_cancel|escape:'html'}
            </a>
            {if $pb_cron}
                <button type="submit" name="submitUpdatePbCronJob" class="btn btn-primary pull-right pb-btn-submit">
                    <i class="icon-save"></i> {$pb_l.btn_save|escape:'html'}
                </button>
            {else}
                <button type="submit" name="submitAddPbCronJob" class="btn btn-primary pull-right pb-btn-submit" disabled>
                    <i class="icon-plus"></i> {$pb_l.btn_add|escape:'html'}
                </button>
            {/if}
        </div>
    </form>
</div>

{* ════════════════════════════════════════════════════════════════════════════
   MODE : LIST
   ════════════════════════════════════════════════════════════════════════════ *}
{else}

{* ── Tabs navigation ──────────────────────────────────────────────────────── *}
<ul class="nav nav-tabs" id="pb-main-tabs" role="tablist">
    <li role="presentation" class="active">
        <a href="#pb-tab-tasks" aria-controls="pb-tab-tasks" role="tab" data-toggle="tab">
            <i class="icon-list"></i> {$pb_l.cron_tasks|escape:'html'}
            <span class="badge">{$pb_crons|count}</span>
        </a>
    </li>
    <li role="presentation">
        <a href="#pb-tab-log" aria-controls="pb-tab-log" role="tab" data-toggle="tab">
            <i class="icon-history"></i> {$pb_l.exec_log|escape:'html'}
            {if $pb_logs}<span class="badge">{$pb_logs|count}</span>{/if}
        </a>
    </li>
</ul>

<div class="tab-content">

{* ── Tab 1 : Tasks ───────────────────────────────────────────────────────── *}
<div id="pb-tab-tasks" role="tabpanel" class="tab-pane active panel pb-tab-panel">
    <div class="panel-heading">
        <i class="icon-list"></i> {$pb_l.cron_tasks|escape:'html'}
        <div class="panel-heading-action">
            <a href="{$pb_base_link|escape:'html'}&newpbcronjob=1" class="btn btn-primary btn-sm">
                <i class="icon-plus"></i> {$pb_l.add_new_task|escape:'html'}
            </a>
        </div>
    </div>

    {if $pb_crons}
    <div class="table-responsive">
        <table class="table pb-crons-table">
            <thead>
                <tr>
                    <th class="pb-col-handle"></th>
                    <th>{$pb_l.col_description|escape:'html'}</th>
                    <th>{$pb_l.col_url|escape:'html'}</th>
                    <th>{$pb_l.col_schedule|escape:'html'}</th>
                    <th>{$pb_l.col_last_run|escape:'html'}</th>
                    <th class="text-center">{$pb_l.col_one_shot|escape:'html'}</th>
                    <th class="text-center">{$pb_l.col_active|escape:'html'}</th>
                    <th class="text-right">{$pb_l.col_actions|escape:'html'}</th>
                </tr>
            </thead>
            <tbody id="pb-crons-tbody" data-sort-url="{$pb_sort_url|escape:'html'}">
            {foreach $pb_crons as $cron}
                <tr class="{if !$cron.active}pb-row-inactive{/if}" data-id="{$cron.id_pb_cronjob}">
                    <td class="pb-col-handle">
                        <i class="icon-bars pb-sort-handle" title="{$pb_l.col_order|escape:'html'}"></i>
                    </td>
                    <td class="pb-col-desc">
                        <strong>{$cron.description|escape:'html'}</strong>
                    </td>
                    <td class="pb-col-url">
                        <span title="{$cron.task|escape:'html'}">{$cron.task_display|escape:'html'}</span>
                    </td>
                    <td>
                        <code>{$cron.schedule|escape:'html'}</code>
                    </td>
                    <td class="pb-col-date">
                        {if $cron.last_run}
                            {$cron.last_run|escape:'html'}
                        {else}
                            <span class="text-muted">{$pb_l.never|escape:'html'}</span>
                        {/if}
                    </td>
                    <td class="text-center">
                        <a href="{$pb_base_link|escape:'html'}&oneshotpbcronjob=1&id_pb_cronjob={$cron.id_pb_cronjob}"
                           title="{$pb_l.toggle_one_shot|escape:'html'}">
                            {if $cron.one_shot}
                                <span class="badge badge-warning pb-badge-oneshot">{$pb_l.yes|escape:'html'}</span>
                            {else}
                                <span class="text-muted">—</span>
                            {/if}
                        </a>
                    </td>
                    <td class="text-center">
                        <a href="{$pb_base_link|escape:'html'}&activepbcronjob=1&id_pb_cronjob={$cron.id_pb_cronjob}"
                           title="{$pb_l.toggle_active|escape:'html'}">
                            {if $cron.active}
                                <i class="icon-check text-success" style="font-size:16px;"></i>
                            {else}
                                <i class="icon-times text-danger" style="font-size:16px;"></i>
                            {/if}
                        </a>
                    </td>
                    <td class="text-right pb-col-actions">
                        <a href="{$pb_base_link|escape:'html'}&updatepbcronjob=1&id_pb_cronjob={$cron.id_pb_cronjob}"
                           class="btn btn-default btn-xs" title="{$pb_l.btn_edit|escape:'html'}">
                            <i class="icon-pencil"></i>
                        </a>
                        <a href="{$pb_base_link|escape:'html'}&runpbcronjob=1&id_pb_cronjob={$cron.id_pb_cronjob}"
                           class="btn btn-info btn-xs" title="{$pb_l.btn_run|escape:'html'}">
                            <i class="icon-play"></i>
                        </a>
                        <a href="{$pb_base_link|escape:'html'}&deletepbcronjob=1&id_pb_cronjob={$cron.id_pb_cronjob}"
                           class="btn btn-danger btn-xs pb-delete-btn"
                           data-desc="{$cron.description|escape:'html'}"
                           title="{$pb_l.btn_delete|escape:'html'}">
                            <i class="icon-trash"></i>
                        </a>
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
    {else}
    <div class="panel-body">
        <p class="text-muted text-center" style="padding:20px 0;">
            <i class="icon-clock-o" style="font-size:32px;display:block;margin-bottom:8px;"></i>
            {$pb_l.no_tasks|escape:'html'}
        </p>
    </div>
    {/if}
</div>

{* ── Tab 2 : Execution log ───────────────────────────────────────────────── *}
<div id="pb-tab-log" role="tabpanel" class="tab-pane panel pb-tab-panel">
    <div class="panel-heading">
        <i class="icon-history"></i> {$pb_l.exec_log|escape:'html'}
        <div class="panel-heading-action pb-log-actions">
            <select id="pb-log-filter" class="form-control input-sm" title="{$pb_l.all_tasks|escape:'html'}">
                <option value="">{$pb_l.all_tasks|escape:'html'}</option>
                {foreach $pb_crons as $cron}
                <option value="{$cron.id_pb_cronjob}">{$cron.description|escape:'html'}</option>
                {/foreach}
            </select>
            <select id="pb-per-page" class="form-control input-sm pb-per-page-select">
                <option value="25" selected="selected">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="200">200</option>
                <option value="500">500</option>
            </select>
            <form method="post" action="{$pb_base_link|escape:'html'}" style="display:inline-block;margin:0;">
                <button type="submit" name="purge_pb_logs" class="btn btn-warning btn-sm"
                        onclick="return confirm('{$pb_l.confirm_purge|escape:'js'}')">
                    <i class="icon-trash"></i> {$pb_l.btn_purge_logs|escape:'html'}
                </button>
            </form>
        </div>
    </div>
    <div class="panel-body pb-purge-url-bar">
        <div class="input-group input-group-sm">
            <span class="input-group-addon"><i class="icon-clock-o"></i></span>
            <input type="text" id="pb-purge-url" class="form-control" value="{$pb_purge_url|escape:'html'}" readonly />
            <span class="input-group-btn">
                <button class="btn btn-default btn-sm" type="button" id="pb-copy-purge-url">
                    <i class="icon-copy"></i>
                </button>
            </span>
        </div>
        <p class="help-block" style="margin-bottom:0;">{$pb_l.auto_purge_help|escape:'html'}</p>
    </div>
    {if $pb_logs}
    <div class="table-responsive">
        <table class="table table-condensed pb-logs-table">
            <thead>
                <tr>
                    <th>{$pb_l.col_date|escape:'html'}</th>
                    <th>{$pb_l.col_task|escape:'html'}</th>
                    <th class="text-center">{$pb_l.col_http|escape:'html'}</th>
                    <th class="text-center">{$pb_l.col_duration|escape:'html'}</th>
                    <th>{$pb_l.col_response|escape:'html'}</th>
                </tr>
            </thead>
            <tbody>
            {foreach $pb_logs as $log}
                <tr class="{if $log.status == 'error'}pb-log-error{/if}" data-cron-id="{$log.id_pb_cronjob}">
                    <td class="pb-col-date">{$log.run_at|escape:'html'}</td>
                    <td><small>{$log.description|default:'—'|escape:'html'}</small></td>
                    <td class="text-center">
                        {if $log.http_code}
                            <span class="label label-{if $log.status == 'success'}success{else}danger{/if}">
                                {$log.http_code}
                            </span>
                        {else}
                            <span class="label label-default">—</span>
                        {/if}
                    </td>
                    <td class="text-center">
                        <small class="text-muted">{$log.duration_ms}ms</small>
                    </td>
                    <td>
                        {if $log.response}
                            <small class="text-muted pb-log-response" title="{$log.response|escape:'html'}">
                                {$log.response|truncate:80:'…'|escape:'html'}
                            </small>
                        {/if}
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
    <div class="panel-body pb-log-footer">
        <span id="pb-log-info" class="text-muted"></span>
        <nav id="pb-log-pagination" aria-label="Log pagination"></nav>
    </div>
    {else}
    <div class="panel-body">
        <p class="text-muted text-center" style="padding:10px 0;">—</p>
    </div>
    {/if}
</div>

</div>{* end tab-content *}

{/if}{* end mode list *}

{* ── Delete confirmation modal ───────────────────────────────────────────── *}
<div class="modal fade" id="pb-delete-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header pb-modal-danger">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="icon-trash"></i> {$pb_l.modal_title|escape:'html'}
                </h4>
            </div>
            <div class="modal-body">
                <p>{$pb_l.modal_confirm|escape:'html'}</p>
                <p id="pb-delete-desc" class="pb-delete-desc"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="icon-times"></i> {$pb_l.btn_cancel|escape:'html'}
                </button>
                <a href="#" id="pb-delete-confirm" class="btn btn-danger">
                    <i class="icon-trash"></i> {$pb_l.btn_delete|escape:'html'}
                </a>
            </div>
        </div>
    </div>
</div>

{* ── Footer : offered by ─────────────────────────────────────────────────── *}
<div class="panel pb-footer-panel">
    <div class="panel-body text-center">
        <small class="text-muted">
            {$pb_l.offered_by|escape:'html'}
            <a href="https://www.pimentbleu.fr/" target="_blank" rel="noopener" title="Piment Bleu - Agence spécialisée développement PrestaShop, création de modules sur mesure">
                <strong>PIMENT BLEU</strong>
            </a>
        </small>
    </div>
</div>
