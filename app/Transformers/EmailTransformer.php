<?php 

namespace App\Transformers;

class EmailTransformer extends Transformer {

    public function transform($email)
    {
        return [
            'id' => $email['id'],
            'from' => $email['from'],
            'to' => $email['to'],
            'subject' => $email['subject'],
            'created_at' => $email['created_at'],
            'read' => $email['read'],
            'favorite' => (boolean) $email['favorite'],
            'is_html' => (boolean) $email['is_html'],
            'is_text' => (boolean) $email['is_text'],
        ];
    }
}