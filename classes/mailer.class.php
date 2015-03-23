<?php

class Mailer {

    private $to = array();
    private $subject;
    private $from;
    private $body;
    private $count;

    private function counter_int() {
        $count = 0;
    }

    private function count() {
        return $count;
    }

    public function mail_to($to_array) {
        $this->counter_int();

        foreach ($to_array as $to_email) {
            if ($this->count)
                $this->send_to .= '';
        }
    }

    private function send() {
        if (
        mail(
            $this->from(),
            $this->subject(),
            $this->message(),
            $headers
        )
        ) {
            $this->response('Your content was committed successfully and your tutor has been notified .');
        }
    }

}

?>