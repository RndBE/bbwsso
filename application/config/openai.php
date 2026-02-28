<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| OpenAI Configuration
|--------------------------------------------------------------------------
|
| Konfigurasi untuk integrasi chatbot dengan OpenAI API.
| Isi 'openai_api_key' dengan API key dari https://platform.openai.com
|
*/

// ISI API KEY OPENAI DI SINI
$config['openai_api_key'] = '';

// Model yang digunakan (gpt-4o-mini lebih hemat, gpt-4o lebih pintar)
$config['openai_model'] = 'gpt-4o-mini';
