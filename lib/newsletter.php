<?php

use Kirby\Email\PHPMailer as Email;

class Newsletter
{

    public static function send($from, $to, $subject, $message, $page, $test)
    {
        $to = $test ? [$to] : Newsletter::getSubscribers(); // Get all the subscribers or set test recipient
        $files = Newsletter::getFiles($page);

        $result = [];
        $log = '';
        $status = 200;

        // Check if we have at least of subscriber or a test recipient
        if (!empty($to)) {
            try {
                foreach ($to as $recipient) {
                    $email = new Email([
                        'from' => $from,
                        'replyTo' => $from,
                        'to' => $recipient,
                        'subject' => $subject,
                        'body' => [
                            'html' => $message,
                        ],
                        'attachments' => $files
                    ]);
                };
                $log = $email->isSent() ? 'Mail has been sent !' : 'Mais has not been sent';
            } catch (Exception $error) {
                $log = $error->getMessage();
                $status = 400;
            }
        } else {
            $log = 'There is no subscriber to send our newsletter to !';
            $status = 400;
        }

        $result = [
            'from' => $from,
            'subject' => $subject,
            'to' => $to,
            'body' => $message,
            'message' => $log,
            'attachments' => $files,
            'status' => $status
        ];

        return $result;
    }

    public static function getSubscribers()
    {
        $to = [];
        //get user list filtered by role
        $users = $kirby->users()->filterBy('role','test');
		$to = $users->pluck('email');
        return $to;
    }

    public static function getFiles($page)
    {
        $files = [];

        foreach ($page->files() as $f) {
            $files[] = $f->root();
        }

        return $files;
    }
}