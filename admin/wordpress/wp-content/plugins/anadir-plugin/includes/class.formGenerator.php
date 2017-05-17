<?php

/**
 * Created by PhpStorm.
 * User: Vadim
 * Date: 14.08.2015
 * Time: 19:27
 */
class formGenerator
{
    private $type;
    private $name;
    private $id;
    private $className;
    private $label;
    private $placeholder;
    private $haveLabel = true;
    private $values;
    private $readOnly = false;
    private $disable = false;
    private $req = false;
    private $showArea = true;
    private $returnHtml = '';
    private $cols = 50;
    private $rows = 25;

    public function __construct($meta){
        //print_r($meta);
        $this->type = $meta['type'];
        $this->name = $meta['name'];

        if(isset($meta['disable'])){
            $this->disable = $meta['disable'];
        }
        if(isset($meta['readOnly'])){
            $this->readOnly = $meta['readOnly'];
        }
        if(isset($meta['req'])){
            $this->req = $meta['req'];
        }
        if(isset($meta['showArea'])){
            $this->showArea = $meta['showArea'];
        }
        if(isset($meta['haveLabel'])){
            $this->haveLabel = $meta['haveLabel'];
        }
        if(isset($meta['placeholder'])){
            $this->placeholder = $meta['placeholder'];
        }
        if(isset($meta['label'])){
            $this->label = $meta['label'];
        }else{
            $this->label = $meta['placeholder'];
        }
        if(isset($meta['className'])){
            $this->className = $meta['className'];
        }
        if(isset($meta['id'])){
            $this->id = $meta['id'];
        }else{
            $this->id = 'formElement'.$this->name;
        }
        if(isset($meta['values'])){
            $this->values = $meta['values'];
        }else{
            if($this->type == 'input-text' || $this->type == 'input-hidden'){
                $this->values = '';
            }else{
                $this->values = array();
            }
        }

        if(isset($meta['rows'])){

            $this->rows = $meta['rows'];
        }

        if(isset($meta['cols'])){

            $this->cols = $meta['cols'];
        }

        if(isset($meta['href'])){
            $this->href = $meta['href'];
        }
        $this->returnHtml = $this->generateHtml();


    }

    public function __toString(){
        return $this->returnHtml;
    }

    public function generateHtml(){

        if($this->haveLabel === true){
            $label = $this->getLabelHtml();
        }else{
            $label = '';
        }
        $template = $this->generateTemplate();
        $controls = $this->generateControls();
        return str_replace(array('%%CONTROLS%%','%%LABEL%%'),array($controls,$label),$template);

    }

    public function generateControls(){


            $result = '';

                if($this->type == 'input-text'){
                    $result .= $this->getInputTextHtml();
                }
                else if($this->type == 'input-hidden'){
                    $result .= $this->getInputHiddenHtml();
                }
                else if($this->type == 'select'){
                    $result .= $this->getSelectHtml();
                }
                else if($this->type == 'input-button'){
                    $result .= $this->getInputButtonHtml();
                }
                else if($this->type == 'label'){
                    $result .= $this->getLabelHtml();
                }
                else if($this->type == 'date'){
                    $result .= $this->getDateHtml();
                }//else if
                else if($this->type == 'time'){
                    $result .= $this->getTimeHtml();
                }//else if
                else if($this->type == 'check-box'){
                    $result .= $this->getCheckBoxTextHtml();
                }//else if
                else if($this->type == 'text-area'){
                    $result .= $this->getTextAreaHtml();
                }//else if
                else if($this->type == 'editor'){

                    $this->getTextEditorHtml();

                }
            return $result;
    }

    public function generateTemplate(){
        if($this->showArea === false){
            $html = '%%CONTROLS%%';
        }else{
            if($this->haveLabel === true){
                $html = '<div class="row" style="width:100%; display: inline-block;"><div class="left">%%LABEL%%</div><div class="right">%%CONTROLS%%</div></div>';
            }else{
                $html = '<div class="row" style="width:100%; display: inline-block;">%%CONTROLS%%</div>';
            }
        }
        return $html;
    }

    public function getTimeHtml(){

        return '<input disabled type="time" name="'.$this->name.'" value="'.$this->values.'" >';
    }

    public function getDateHtml(){

        return '<input disabled type="date" name="'.$this->name.'" value="'.$this->values.'" >';
    }
    
    public function getLabelHtml(){

        return '<label for="'.$this->id.'">'.$this->label.'</label>';
    }

    public function getInputTextHtml(){

        return '<input style="width:100%;" type="text" class="'.$this->className.'" id="'.$this->id.'" name="'.$this->name.'" value="'.$this->values.'" placeholder="'.$this->placeholder.'" >';

    }

    public function getTextAreaHtml(){

        $text = '';
        if(count($this->values) == 0){
            $text = '';
        }
        else{
            $text =  $this->values;
        }
        return '<textarea rows="'.$this->rows.'" cols="'.$this->cols.'" value="'.$text.'" style="width:100%;" class="'.$this->className.'" id="'.$this->id.'" name="'.$this->name.'" placeholder="'.$this->placeholder.'" >'.$text.'</textarea>';

    }

    public function getCheckBoxTextHtml(){

        if(!empty($this->values) && $this->values == 'on' ){
            return '<input type="checkbox" onclick=" if(this.getAttribute(\'value\') == \'on\'){ this.setAttribute(\'value\',\'off\'); } else{ this.setAttribute(\'value\',\'on\');} " class="'.$this->className.'" value="on"  id="'.$this->id.'" name="'.$this->name.'" checked>';
        }//if
        else{
            return '<input type="checkbox" onclick=" if(this.getAttribute("value") == "on"){ this.setAttribute("value","off"); } else{ this.setAttribute("value","on");} " class="'.$this->className.'" value="off" id="'.$this->id.'" name="'.$this->name.'" placeholder="'.$this->placeholder.'" >';
        }//else


    }

    public function getInputButtonHtml(){

        return '<a href="'.$this->href.'"><input type="button" class="button button-primary button-large" name="'.$this->name.'" value="'.$this->values.'"  ></a>';

    }
    
    public function getInputHiddenHtml(){

        return '<input style="width:100%;" type="hidden" name="'.$this->name.'" value="'.$this->values.'" >';

    }

    public function getSelectHtml(){

        $html = '<select name="'.$this->name.'" class="'.$this->className.'" id="'.$this->id.'">';

        foreach($this->values as $key=>$item){
            $html .= '<option value="'.$key.'" '.($item['active']==true ? ' selected ' : '').'>'.$item['name'].'</option>';
        }
        $html .='</select>';

        return $html;

    }

    public function getTextEditorHtml(){

        $text = '';
        if(count($this->values) == 0){
            $text = '';
        }
        else{
            $text =  $this->values;
        }

        $editor_id = $this->id;

        wp_editor( $text, $editor_id , array(
            'wpautop' => 1,
            'teeny'=>false,
            'textarea_name' => $this->name,
            'tinymce' =>true

        ));

    }

}