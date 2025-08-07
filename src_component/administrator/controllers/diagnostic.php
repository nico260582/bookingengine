<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Mail\MailHelper;

class BookingmanagerControllerDiagnostic extends JControllerLegacy
{
    public function sendTestEmail()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        $app = Factory::getApplication();
        $input = $app->input;
        $recipient = $input->post->getString('recipient_email', '');

        if (!MailHelper::isEmailAddress($recipient)) {
            $app->enqueueMessage('Invalid email address provided.', 'error');
            $this->setRedirect('index.php?option=com_bookingmanager&view=diagnostic');
            return;
        }

        try {
            $config = Factory::getConfig();
            $mailer = Factory::getMailer();
            $sender = [(string)$config->get('mailfrom'), (string)$config->get('fromname')];
            
            $mailer->setSender($sender);
            $mailer->addRecipient($recipient);
            $mailer->setSubject('Joomla Mailer Diagnostic Test from Booking Manager');
            $mailer->setBody('<h1>Success!</h1><p>If you have received this email, your Joomla mail configuration is working correctly.</p>');
            $mailer->isHtml(true);

            if ($mailer->send() !== true) {
                $app->enqueueMessage('Joomla\'s Mailer->send() method returned false. This indicates a configuration problem in Global Configuration > Mail Settings.', 'error');
            } else {
                $app->enqueueMessage('Test email has been sent to ' . $recipient . '. Please check your inbox (and spam folder).', 'message');
            }
        } catch (\Exception $e) {
            $app->enqueueMessage('An exception was caught: ' . $e->getMessage(), 'error');
        }
        $this->setRedirect('index.php?option=com_bookingmanager&view=diagnostic');
    }
}