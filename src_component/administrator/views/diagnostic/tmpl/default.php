<?php
defined('_JEXEC') or die;
?>

<div id="j-sidebar-container" class="span2">
    <?php // Sidebar is rendered by the layout ?>
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

    <fieldset class="well">
        <legend>Sample Data Management</legend>
        <p>Install the sample data to get a feel for how the component works. This will populate the component with sample properties, suppliers, rates, etc.</p>
        <p><strong>Warning:</strong> This will add data to your database. It is recommended to only do this on a fresh installation or a test site.</p>
        <a href="<?php echo JRoute::_('index.php?option=com_bookingmanager&task=diagnostic.installSampleData&' . JSession::getFormToken() . '=1'); ?>" class="btn btn-primary" onclick="return confirm('Are you sure you want to install the sample data? This action cannot be undone.');">Install Sample Data</a>
    </fieldset>

    <form action="<?php echo JRoute::_('index.php?option=com_bookingmanager&task=diagnostic.sendTestEmail'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
        <fieldset class="well">
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