<?php

namespace App\Support;

use App\Models\Language;
use App\Models\Word;

if (!defined("IN_CMS")) {
    http_response_code(401);
    die;
}

class LC
{
    protected static $instance;  // object instance
    private $array = [];
    private $lang = 0;
    private $types = 2;

    private function __construct()
    {
        global $translateLangId;

        if (isset($_GET["table"]) || stristr($_SERVER['REQUEST_URI'], "/" . ADMIN_FOLDER . "/")) {
            $translateLangId = getAdminLang();
            $this->types = 1;
        }
        $this->lang = $translateLangId;

        self::getAll($this->lang);

    }

    private function __clone()
    { /* ... @return Singleton */
    }

    public function __wakeup()
    { /* ... @return Singleton */
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            $class = __CLASS__;
            self::$instance = new $class();
        }

        return self::$instance;
    }

    private function getAll($lang)
    {
        $query = Word::where('lang_id', $lang);

        if ($this->types === WORDS_ADMIN) {
            $query->where('type', WORDS_ADMIN);
        } else {
            $query->where('type', '!=', WORDS_ADMIN);
        }

        $query->get()->each(function ($word) {
            $this->array[$word->code][$word->type] = $word->content;
        });
    }

    public function setType($type)
    {
        $this->types = $type;
    }

    public function getType()
    {
        return $this->types;
    }

    public function getOne($index)
    {
        if (!empty($this->array[$index][$this->types])) {
            return parseTextWithVars($this->array[$index][$this->types]);
        } else {
            $this->insert($index, $this->types);
            $this->array[$index][$this->types] = $index;
            return parseTextWithVars($index);
        }
    }

    private function insert(string $index): void
    {
        if (empty($index)) return;

        $existing = Word::where('code', $index)
            ->where('type', $this->types)
            ->where('hash', '!=', '')
            ->first();

        if ($existing) {
            Word::create([
                'lang_id' => $this->lang,
                'code' => $index,
                'content' => $index,
                'hash' => $existing->hash,
                'type' => $this->types,
            ]);
            return;
        }

        $hash = md5(time() . uniqid());

        Language::all()->each(function ($lang) use ($index, $hash) {
            Word::create([
                'lang_id' => $lang->id,
                'code' => $index,
                'content' => $index,
                'hash' => $hash,
                'type' => $this->types,
                'link' => $_SERVER["REQUEST_URI"],
                'add_tm' => time(),
            ]);
        });
    }
}
