<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Webhook_wa extends CI_Controller
{
    public function index()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Method Not Allowed', 405);
            return;
        }

        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true);

        if (!is_array($payload)) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['ok' => false, 'error' => 'invalid_json']));
            return;
        }

        $msg = $this->_extract_message($payload);
        $sessionId = $this->_extract_session_id($payload);

        $chatId = (string) ($msg['chatId'] ?? $msg['chat_id'] ?? $msg['from'] ?? '');
        $body = trim((string) ($msg['body'] ?? $msg['text'] ?? $msg['content'] ?? ''));

        if ($chatId === '' || $body === '') {
            $this->output
                ->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode(['ok' => true, 'skip' => 'no_message']));
            return;
        }

        $reply = $this->_ask_bot($body, $chatId);
        if ($reply !== '') {
            $this->_send_text($sessionId, $chatId, $reply);
        }

        $this->output
            ->set_status_header(200)
            ->set_content_type('application/json')
            ->set_output(json_encode(['ok' => true]));
    }

    private function _extract_message(array $payload)
    {
        if (isset($payload['data']) && is_array($payload['data']))
            return $payload['data'];
        if (isset($payload['message']) && is_array($payload['message']))
            return $payload['message'];
        if (isset($payload['payload']) && is_array($payload['payload']))
            return $payload['payload'];
        return $payload;
    }

    private function _extract_session_id(array $payload)
    {
        $sid = (string) ($payload['sessionId'] ?? $payload['session_id'] ?? $payload['session'] ?? '');
        if ($sid !== '')
            return $sid;
        return 'default';
    }

    private function _ask_bot($text, $chatId)
    {
        $url = rtrim(base_url(), '/') . '/chatbot/ask';

        $payload = json_encode([
            'uuid' => md5((string) $chatId),
            'message' => (string) $text
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);

        $resp = curl_exec($ch);
        $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http < 200 || $http >= 300 || !$resp)
            return '';

        $j = json_decode($resp, true);
        if (!is_array($j))
            return '';

        if (isset($j['message']['content']) && is_string($j['message']['content']))
            return trim($j['message']['content']);
        if (isset($j['content']) && is_string($j['content']))
            return trim($j['content']);
        if (isset($j['reply']) && is_string($j['reply']))
            return trim($j['reply']);

        return '';
    }

    private function _send_text($sessionId, $chatId, $text)
    {
        $wwebBase = 'http://72.60.78.159:3000';
        $apiKey = '';
        $url = rtrim($wwebBase, '/') . '/client/sendMessage/' . rawurlencode((string) $sessionId);

        $payload = json_encode([
            'chatId' => (string) $chatId,
            'contentType' => 'string',
            'content' => (string) $text,
            'options' => (object) []
        ]);

        $headers = ['Content-Type: application/json', 'Accept: application/json'];
        if ($apiKey !== '')
            $headers[] = 'x-api-key: ' . $apiKey;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_exec($ch);
        curl_close($ch);
    }
}