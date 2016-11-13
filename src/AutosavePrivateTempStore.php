<?php

/**
 * @file
 * Contains Drupal\autosave\AutosavePrivateTempStore.
 */

namespace Drupal\autosave;

use Drupal\user\PrivateTempStore;

class AutosavePrivateTempStore extends PrivateTempStore {


   /**
   * Returns all the items in the store.
   *
   * @return array
   */
  public function getAll() {
    $keys = $this->storage->getAll();
    $items = array_filter($keys, function($key) {
      return $this->getOwner() === $key->owner;
    });
    return $items;
  }


   /**
   * Deletes all the data from the store and releases the lock on it.
   *
   * @throws \Drupal\user\TempStoreException
   */
  public function deleteAll() {
    $keys = $this->getAll();
    foreach ($keys as $key => $data) {
      $parts = explode(':', $key);
      $this->delete($parts[1]);
    }
  }

}
