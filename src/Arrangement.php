<?php 
namespace Delta;

class Arrangement{
  public $pameldte;
  public $betalte;
  public $pris_delta;
  public $pris_andre;
  public $sted;
  public $tidspunkt;
  public $kleskode;
  public $bruker_id;
  public $post_id;
  public $status;
  public $vis_pameldte;
  public $tilgjengelige_plasser;
  function __construct($post_id){
    $this->post_id = $post_id;
    $this->bruker_id = get_current_user_ID();
    $this->handlePost();
    $this->settFelter();
    $this->settStatus();
  }
  public function settFelter(){
    $this->tidspunkt = get_field('tidspunkt', $post_id);
    $this->sted = get_field('sted', $post_id);
    $this->pameldte = get_field('pameldte', $post_id);
    $this->betalte = get_field('betalte', $post_id);
    $this->tidspunkt = get_field('tidspunkt', $post_id);
    $this->kleskode = get_field('kleskode', $post_id);
    $this->pris_delta = get_field('pris_delta', $post_id);
    $this->pris_andre = get_field('pris_andre', $post_id);
    $this->vis_pameldte = get_field('vis_pameldte', $post_id);
    $this->tilgjengelige_plasser = get_field('tilgjengelige_plasser', $post_id);
  }
  public function erMed($liste = false){
    if(!$liste)
      $liste = $this->pameldte;
    return $this->iListe($liste);
  }
  public function iListe($liste){
    if(! is_array($liste))
      return false;
    foreach($liste as $bruker){
      if($this->bruker_id == $bruker["ID"])
        return true;
    }
    return false;
  }
  public function harBetalt(){
    return $this->iListe($this->betalte);
  }
  public function hentDato(){
    return date_i18n('d. F Y',strtotime(get_field('tidspunkt')));
  }
  public function hentTidspunkt(){
    return date_i18n('H:i',strtotime(get_field('tidspunkt')));
  }
  public function erGratis(){
    return intval($this->pris_delta) == 0;
  }
  public function printPris(){
    return $this->pris_delta . ',- &nbsp; // ' . $this->pris_andre . ',-';
  }
  public function hentPris(){
    return number_format($this->pris_delta, 2, '.', '');
  }
  public function visPameldte(){
    return $this->vis_pameldte;
  }
  public function tellPameldte(){
    return is_array($this->pameldte) ? count($this->pameldte) : 0;
  }
  public function kanBliMed(){
    return true;
  }
  private function meldPaa($bruker_id = false, $post_id = false){
    if(!$bruker_id)
      $bruker_id = $this->bruker_id;
    if(!$post_id)
      $post_id = $this->post_id;
      ACFUpdater::leggTil('pameldte', $bruker_id, $post_id);
  }
  private function betal($bruker_id = false, $post_id = false){
    if(!$bruker_id)
      $bruker_id = $this->bruker_id;
    if(!$post_id)
      $post_id = $this->post_id;
      ACFUpdater::leggTil('betalte', $bruker_id, $post_id);
  }
  private static function meld_av($bruker_id = false, $post_id = false){
    if(!$bruker_id)
      $bruker_id = $this->bruker_id;
    if(!$post_id)
      $post_id = $this->post_id;
      ACFUpdater::fjern('pameldte', $bruker_id, $post_id);
  }
  public function handlePost(){
    if(!isset($_POST) && !empty($_POST))
      return false;
    if(!isset($_POST['bruker']) && empty($_POST['bruker']))
      return false;
    if(!isset($_POST['handling']) && empty($_POST['handling']))
      return false;
    if(!isset($_POST['arrkom']) && empty($_POST['arrkom']))
      return false;
    switch($_POST['handling']){
      case "meld_paa":
        $this->meldPaa($_POST['bruker'], $_POST['arrkom']);
        break;
      case "meld_av":
        $this->meldAv($_POST['bruker'], $_POST['arrkom']);
        break;
      case "betalt":
        $this->betal($_POST['bruker'], $_POST['arrkom']);
        break;
    }
  }
  public function settStatus(){
    if($this->erGratis()){
      // Det er gratis - bare tenk på påmeldte
      if($this->erMed($this->pameldte)){
        $this->status = "Du er meldt på!";
      }else{
        $this->status = false;
      }
    }else{
      // Det koster penger, her er det de betalte som gjelder
      if($this->harBetalt()){
        $this->status = "Du er meldt på og din betaling er registrert!";
      }else if($this->erMed($this->pameldte)){
        $this->status = "Du er meldt på men din betaling er ikke registrert enda";
      }else{
        $this->status = false;
      }
    }

  }
}