<?php

/**
 * @file
 * Contains Drupal\autosave\AutosavePrivateTempStoreFactory.
 */

namespace Drupal\autosave;

use Drupal\Core\KeyValueStore\KeyValueExpirableFactoryInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\user\PrivateTempStoreFactory;
use Drupal\autosave\AutosavePrivateTempStore;

/**
 * Creates a AutosavePrivateTempStore object for a given collection.
 */
class AutosavePrivateTempStoreFactory extends PrivateTempStoreFactory {
 
  /**
   * Constructs a Drupal\autosave\AutosavePrivateTempStoreFactory object.
   *
   * @param \Drupal\Core\KeyValueStore\KeyValueExpirableFactoryInterface $storage_factory
   *   The key/value store factory.
   * @param \Drupal\Core\Lock\LockBackendInterface $lock_backend
   *   The lock object used for this data.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current account.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param int $expire
   *   The time to live for items, in seconds.
   */
  function __construct(KeyValueExpirableFactoryInterface $storage_factory, LockBackendInterface $lock_backend, AccountProxyInterface $current_user, RequestStack $request_stack, $expire = 1210000) {
    parent::__construct($storage_factory, $lock_backend, $current_user, $request_stack, $expire);
  }

  /**
   * Creates an AutosavePrivateTempStore.
   *
   * @param string $collection
   *   The collection name to use for this key/value store. This is typically
   *   a shared namespace or module name, e.g. 'views', 'entity', etc.
   *
   * @return \Drupal\user\PrivateTempStore
   *   An instance of the key/value store.
   */
  function get($collection) {
    // Store the data for this collection in the database.
    $storage = $this->storageFactory->get("autosave.private_tempstore.$collection");
    return new AutosavePrivateTempStore($storage, $this->lockBackend, $this->currentUser, $this->requestStack, $this->expire);
  }

}
