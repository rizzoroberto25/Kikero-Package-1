<?php
namespace App\Services;
use App\Services\Html;
use Illuminate\Database\Eloquent\Model;
use ResponseService;

class TextManager extends Html {

    public $id;
    public $fields;
    public $languages;
    public $CKcounter;
    public $table;
    public $record;
    public $TranslationModel;

    public function __construct() {
        $this->languages = \Config::get("project.app_languages");
        $this->CKcounter = 0;
    }

    public function setFields(array $fields) {
        if(!count($fields)>0) return NULL;
    }

    public function printTestiManager(string $key, array $objects) {

        if(empty($key)) return false;
        if(!count($objects)>0) return false;

        $html = "<div class=\"default-tab\">";
        $this->setHtml($html);
        
        $html = "<nav><div class=\"nav nav-tabs\" role=\"tablist\">";

        $counter = 0;
        foreach($this->languages as $language) {
            //if($language['active']) {
            $cssClass = "nav-item nav-link";
            if($counter == 0) {
                $cssClass .= " active";
            }
            $html .= "
                <a class=\"".$cssClass."\" data-toggle=\"tab\" href=\"#".$key."-".$language['code']."\" role=\"tab\" aria-controls=\"nav-home\" aria-selected=\"true\">".$language['admin']."</a>
            ";
            $counter++;
            //}
        }

        $html .= "</div></nav>";
        $this->addHtml($html);

        $this->addHtml("<div class=\"tab-content pl-3 pt-2\">");

        $counter = 0;
        foreach($this->languages as $language) {
            if($language['active']) {
                $cssClass = "tab-pane fade";
                if($counter == 0) {
                    $cssClass .= " show active";
                }

                $html = "<div class=\"".$cssClass."\" id=\"".$key."-".$language['code']."\" role=\"tabpanel\" aria-labelledby=\"".$key."-".$language['code']."-tab\">";
                
                //$html .= "Contenuto ".$language['admin'];
                foreach($objects as $object) {

                    switch($object['type']) {

                        case "cke":
                            $html .= $this->getCKEditor($language['code'], $object, $key);
                            break;

                        case "textarea":
                            $html .= $this->textarea($language['code'], $object);
                            break;

                        default:
                            $html .= $this->text_input($language['code'], $object);
                            break;
                    }

                }

                $html .= "</div>";

                $this->addHtml($html);
                $counter++;
            }
        }

        $this->addHtml("</div>");


        $html = "</div>";
        $this->addHtml($html);
        $this->printHtml();
        
        //return true;
    }


    public static function getTextFromValues(array $values, $language) {
        $value = "";
        foreach($values as $item) {
            if(!empty($item['language']) && $item['language'] == $language) {
                if(!empty($item['value'])) return $item['value'];
            }
        }
        return $value;
    }

    private function text_input($language, $object) {

        $class = "form-control";
        if($object['required']) {
            $class .=" required";
        }
        $regExpAttr = "";
        if($object['reg_exp'] !== false) {
            $class .=" regexp_val";
            $regExpAttr = " data-regexp='".$object['reg_exp']."'";
        }

        $value = "";
        if(isset($object['values'])) {
            $value = self::getTextFromValues($object['values'] ,$language);
        }

        $html = "
        <div class=\"form-group\">
            <label class=\"control-label mb-1\">".$object['label']."</label>
            <input name=\"translations[".$object['field']."][".$language."]\" type=\"text\" class=\"".$class."\"".$regExpAttr." value=\"".$value."\"> 
            <div class=\"error-message alert alert-danger danger\"></div>
        </div>
        ";

        return $html;
    }

    private function getCKEditor($language, $object, $key) {
        
        $class = "form-control";
        if($object['required']) {
            $class .=" required";
        }

        $this->CKcounter++;

        $value = "";
        if(isset($object['values'])) {
            $value = self::getTextFromValues($object['values'] ,$language);
        }

        $html = "
        <div class=\"form-group\">
            <label class=\"control-label mb-1\">".$object['label']."</label>
            <textarea name=\"translations[".$object['field']."][".$language."]\" id=\"CKE_".$key."_".$this->CKcounter."\" class=\"ck_text\" cols=\"50\" rows=\"4\">".$value."</textarea>
            <div class=\"error-message alert alert-danger danger\"></div>
        </div>

        <script>
            CKEDITOR.replace( 'CKE_".$key."_".$this->CKcounter."');
        </script>

        ";

        return $html;
    }

    private function textarea($language, $object) {
        $class = "form-control";
        if($object['required']) {
            $class .=" required";
        }
        $regExpAttr = "";
        if($object['reg_exp'] !== false) {
            $class .=" regexp_val";
            $regExpAttr = " data-regexp='".$object['reg_exp']."'";
        }

        $value = "";
        if(isset($object['values'])) {
            $value = self::getTextFromValues($object['values'] ,$language);
        }

        $html = "
        <div class=\"form-group\">
            <label class=\"control-label mb-1\">".$object['label']."</label>
            <textarea name=\"translations[".$object['field']."][".$language."]\" class=\"".$class."\"".$regExpAttr.">".$value."</textarea>
            <div class=\"error-message alert alert-danger danger\"></div>
        </div>
        ";

        return $html;
    }

    public function setOldValues(string $key, array $values = []) {

        //$source_values = NULL;
        if(!empty(old($key))) {
            //$source_values = old($key);
            return old($key);
        }

        if(count($values)>0) {
            return $values;
        }
        
    }

    public function storeTranslations(string $namespace, int $model_id, array $translations, $TranslationsModel = "App\Models\Translation") {

        if(!class_exists($namespace)) {
            /*
            $ResponseService->setMessage("generic.errors.inexistent_model");
            $ResponseService->setStatus('400');
            return $ResponseService->getResponse();
            */
            ResponseService::setResponse(['message'=>__('generic.errors.inexistent_model'), 'status'=>400, ]);
            ResponseService::getResponse();
        }
        
        foreach($translations as $key=>$textes) {
            foreach($this->languages as $language) {
                if(array_key_exists($language['code'], $textes)) {
                    $ModelClass = new $TranslationsModel();
                    $ModelClass->model = $namespace;
                    $ModelClass->model_id = $model_id;
                    $ModelClass->field = $key;
                    $ModelClass->lang = $language['code'];
                    $ModelClass->textval = $textes[$language['code']];
                    if(!$ModelClass->save()) {
                        ResponseService::setResponse(['result'=>false, 'message'=>__("generic.errors.saving_translation_failed"), 'status'=>500, ]);
                        return ResponseService::getResponse();
                    }
                }
            }
        }

        ResponseService::setResponse(['result'=>true, 'status'=>200, ]);

        return ResponseService::getResponse();

        
    }

    public function isAnAppLanguage(string $language):bool {
        foreach($this->languages as $appLang) {
            if($appLang == $language) {
                return true;
            }
        }
        return false;
    } 

}