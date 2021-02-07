<?php
namespace Dobis\Curl;

class Query
{
    private $params = [
        'method' => 'POST',
        'timeout' => 30,
        'options' => [],
        'fields' => [],
        'headers' => [],
    ];
    private $errors = [];
    private $url;
    private $result;

    public function url(string $url): self
    {
        if (isset($url)) {
            $this->url = $url;
        }

        return $this;
    }

    public function setFields(array $fields, bool $json = false): self
    {
        if (count($fields)) {
            $this->params['fields'] = ($json) ? json_encode($fields) : $fields;
        }

        return $this;
    }

    public function setHeaders(array $headers): self
    {
        if (count($headers)) {
            $this->params['headers'] = $headers;
        }

        return $this;
    }

    public function setOptions(array $options): self
    {
        if (count($options)) {
            $this->params['options'] = $options;
        }

        return $this;
    }

    public function setTimeout(int $seconds): self
    {
        if ($seconds > 0) {
            $this->params['timeout'] = $seconds;
        }

        return $this;
    }

    private function execute(): bool
    {
        if (!$this->url) {
            $this->errors[] = "Не добавлен url";
            return false;
        }

        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $this->url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->params['timeout']);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->params['timeout']);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_FAILONERROR, true);

            if ($this->params['method'] == "POST") {
                curl_setopt($curl, CURLOPT_POST, true);
            }

            if (count($this->params['headers'])) {
                curl_setopt($curl, CURLOPT_HTTPHEADER, $this->params['headers']);
            }

            // options
            if ($this->params['options']['SSL']) {
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
            }

            if ($this->params['options']['HTTPAUTH']) {
                curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            }

            if ($this->params['options']['ENCODING']) {
                curl_setopt($curl, CURLOPT_ENCODING, $this->params['options']['ENCODING']);
            }

            if ($this->params['options']['USERPWD']) {
                curl_setopt($curl, CURLOPT_USERPWD, $this->params['options']['USERPWD']);
            }

            if ($this->params['fields']) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $this->params['fields']);
            }

            $this->result = curl_exec($curl);
            curl_close($curl);

            return ($this->result) ? true : false;
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    public function get(): bool
    {
        $this->params['method'] = "GET";
        return $this->execute();
    }

    public function post(): bool
    {
        $this->params['method'] = "POST";
        return $this->execute();
    }

    public function getDataToArray(): ?array
    {
        $data = ($this->result) ? json_decode($this->result, true) : null;
        return (is_array($data)) ? $data : null;
    }

    public function getDataToObject(): ?object
    {
        $data = ($this->result) ? json_decode($this->result) : null;
        return (is_object($data)) ? $data : null;
    }

    public function getDataToText(): ?string
    {
        return ($this->result) ? (string) $this->result : null;
    }

    public function response(): bool
    {
        return ($this->result) ? true : false;
    }

    public function showErrors(): ?array
    {
        return (count($this->errors)) ? $this->errors : null;
    }
}