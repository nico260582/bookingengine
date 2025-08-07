<?php
defined('_JEXEC') or die;
?>

<div id="j-sidebar-container" class="span2">
    <?php echo JHtmlSidebar::render(); ?>
</div>
<div id="j-main-container" class="span10">
    <fieldset class="well">
        <legend>Schema Health Check</legend>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Check Name</th>
                    <th style="width:10%;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($this->schemaChecks as $check) : ?>
                    <tr>
                        <td><?php echo $this->escape($check->name); ?></td>
                        <td>
                            <?php if ($check->status) : ?>
                                <span class="badge badge-success">OK</span>
                            <?php else : ?>
                                <span class="badge badge-important">Missing</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </fieldset>

    <form action="<?php echo JRoute::_('index.php?option=com_bookingmanager&task=diagnostic.sendTestEmail'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
        <fieldset>
            <legend>Email Test</legend>
            <p>This tool will test your Joomla website's core email sending functionality. It uses the settings from your Global Configuration.</p>
            <dl>
                <dt><strong>Mailer Type:</strong></dt>
                <dd><?php echo $this->escape($this->mailSettings->mailer); ?></dd>
                <dt><strong>From Email:</strong></dt>
                <dd><?php echo $this->escape($this->mailSettings->mailfrom); ?></dd>
                 <dt><strong>From Name:</strong></dt>
                <dd><?php echo $this->escape($this->mailSettings->fromname); ?></dd>
            </dl>
            <hr/>
            <div class="control-group">
                <div class="control-label"><label for="recipient_email">Recipient Email Address</label></div>
                <div class="controls"><input type="email" name="recipient_email" id="recipient_email" required class="input-large validate-email"></div>
            </div>
            <div class="control-group">
                <div class="controls"><button type="submit" class="btn btn-primary">Send Test Email</button></div>
            </div>
        </fieldset>
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>