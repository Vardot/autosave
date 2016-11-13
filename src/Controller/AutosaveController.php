<?php
namespace Drupal\autosave\Controller;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormState;
use Drupal\Core\Routing;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Form\FormBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;

use Drupal\Core\Form\FormAjaxResponseBuilder;

/**
 * Default controller for the autosave module.
 */
class AutosaveController extends ControllerBase {

   /**
   * Autosaves the node.
   */
  public function autosave() {
    
    $path = $_POST['autosave_form_path'];
    $form_id = $_POST['form_id'];
    $post_clone = $_POST;
    unset($post_clone['autosave_form_path']);
    
    $post_clone['timestamp'] = REQUEST_TIME;
    
    $serialized = serialize($post_clone);

    // Check if node has just been saved - if it has then it's because AS ajax
    // fired off as user was submitting
    // if it had just been submitted - no need to AS now
    // - easy to figure out if we are submitting an edit to existing node
    // - little harder if we have just added a node.
    $path_args = explode("/", $path);
    // Update case.
    $nid = 0;
    if (is_numeric($path_args[2])) {
      $submitted = \Drupal::entityManager()->getStorage('node')->load(intval($path_args[2]));
      $nid = intval($path_args[2]);
    }

    if (!$submitted || (REQUEST_TIME - $submitted->changed) > 10) {
      
      $autosave_private_tempstore = \Drupal::service('autosave.private_tempstore')->get('autosave');
      $autosave_private_tempstore->set('#formid:' . $form_id . '#path:' . $path, $serialized);

      $response = new JsonResponse(array("message" => "Autosaved", 200));
      return $response;
    }
    
    $response = new JsonResponse(array("message" => "Nothing to autosave", 200));
    return $response;
  }

  /**
   * Restore the autosaved values, and repopulated with autosaved data.
   *
   * @param string $formid
   *   The ID of the form to reload.  This should be in Javascript format, vis,
   *   using - instead of _.
   * @param int $timestamp
   *   The timestamp at which the autosaved form was saved.  This is used to
   *   differentiate between different people mucking with the same form.
   */
  public function restore($formid, $timestamp, $path) {
    
    // Convert the form ID back to the PHP version.
    $form_id = str_replace("-", "_", $formid);
   
    $autosave_private_tempstore = \Drupal::service('autosave.private_tempstore')->get('autosave');
    $serialized_data = $autosave_private_tempstore->get('#formid:' . $form_id . '#path:' . $path);
    

    
    if (isset($serialized_data)) {
      $form_data = unserialize($serialized_data);
      

      
      if (isset($record->nid) && $record->nid != 0) {
        
        // Load the node object for the node id.
        $node = \Drupal::entityManager()->getStorage('node')->load($record->nid);

        // #TESTING only . Reset the title for this node from the autosaved form.
        $node->set('title', $form_data['title']);

        // Create a form object for this node.
        $form = \Drupal::service('entity.manager')
          ->getFormObject('node', 'default')
          ->setEntity($node);
        
       
        // Build the form from the from object.
        $build_form = \Drupal::formBuilder()->getForm($form);
        
        global $base_url;
       
        // Reset the Action url.
        $build_form['#action'] = $base_url . $path;      
        
        // Get the rendered from.
        $randered_form = \Drupal::service("renderer")->render($build_form);
        
        // Send an AJAX response type of replace command to replace the current 
        // form with the autosaved one.
        $response = new AjaxResponse();
        $response->addCommand(new ReplaceCommand(
          '#' . $formid,
          $randered_form));
        return $response;
      }
      else {
        
        // The form_data when we do have new form.
        $randered_form = "<h1>New form</h1>";

          
        $response = new AjaxResponse();
        $response->addCommand(new ReplaceCommand(
          '#' . $formid,
          $randered_form));
        return $response;
      }
      
      
    }
    else {
      $response = new JsonResponse(array("message" => "Nothing to restore", 200));
      return $response;
    }
  }

  
  /**
   * Delete autosaved form.
   * 
   * @param type $nid
   *   The node id
   */
  public static function deleteAutosavedForm($nid) {
    $autosave_private_tempstore = \Drupal::service('autosave.private_tempstore')->get('autosave');
    $autosave_private_tempstore->deleteAll();
  }

}
