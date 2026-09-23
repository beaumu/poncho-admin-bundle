UPGRADE FROM 1.1 to 1.2
=======================

Security
--------

 * Deprecate `BaseAdminUser::eraseCredentials()`, use `erasePlainPassword()` instead

   `UserManager::updatePassword()` now calls `erasePlainPassword()`. If your admin
   user entity overrides `eraseCredentials()` to clear data of its own, move that
   logic into `erasePlainPassword()` so it keeps running after a password change:

   Before:
   ```php
   class AdminUser extends BaseAdminUser
   {
       public function eraseCredentials(): void
       {
           parent::eraseCredentials();
           $this->totpSecret = null;
       }
   }
   ```

   After:
   ```php
   class AdminUser extends BaseAdminUser
   {
       public function erasePlainPassword(): void
       {
           parent::erasePlainPassword();
           $this->totpSecret = null;
       }
   }
   ```

   Anything that must never reach the session belongs in `__serialize()`
   instead: Symfony 7.3+ no longer calls `eraseCredentials()` after login once
   the method is marked `#[\Deprecated]`, which it now is.
