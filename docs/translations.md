# Translations

The bundle's strings live in the `PonchoAdmin` domain: 71 keys, shipped in **English only**.

## Translating the bundle into your language

Symfony merges your application's translations over the bundle's, so a file in your `translations/`
directory translates every string rendered by PHP and Twig — labels, buttons, the login and reset
pages, the reset e-mail:

```yaml
# translations/PonchoAdmin.nl.yaml
action:
    save: Opslaan
    cancel: Annuleren
label:
    search...: Zoeken...
```

Only the keys you define are replaced.

!> **JavaScript strings are not covered.** The 13 strings used by the browser — the confirmation
dialog's buttons, the selection counter, autocomplete messages, error toasts — are compiled into
`poncho_admin.js` when the bundle is built, from the bundle's own `translations/` directory. Your
application's files never reach them.

!> **Known issue:** those JavaScript strings do not fall back to English. `Translator` ignores its
fallback locale, so on a page whose `<html lang>` is not `en`, they appear as raw keys —
`action.confirm`, `datatable.no_item_selected`. Until fixed, or until the bundle ships your language,
this affects every non-English admin.

## Adding a language to the bundle

The JavaScript strings can only be translated in the bundle itself — a welcome
[contribution](contributing/index):

1. Copy `translations/PonchoAdmin.en.php` to `translations/PonchoAdmin.<locale>.php` and translate it.
2. Regenerate the JavaScript catalogue: `ddev exec bin/generate-translation`
3. Rebuild the assets: `ddev exec yarn build`
4. Commit all three — the PHP file, `assets/translator/poncho-admin-translations.json` and `public/`.

The date picker has its own locales: flatpickr's French is bundled, other languages need importing in
`assets/form/DatePicker.js`.

## Your own strings

Labels you set yourself — column labels, menu items, action texts — use the domain you give them,
`messages` by default. See each component's `translation_domain` option.

## Reference

| Key | English |
| --- | --- |
| `label.yes` | Yes |
| `label.no` | No |
| `label.name` | Name |
| `label.email` | Email |
| `label.created_at` | Created at |
| `label.active` | Active |
| `label.firstname` | Firstname |
| `label.lastname` | Lastname |
| `label.password` | Password |
| `label.search...` | Search... |
| `label.my_account` | My Account |
| `label.notifications` | Notifications |
| `label.enter_your_password` | Enter your password |
| `label.enter_your_new_password` | Enter your new password |
| `label.welcome` | Welcome |
| `label.enter_your_email` | Enter your email |
| `label.newpassword` | New password |
| `label.password_confirm` | Confirm password |
| `label.confirm_your_new_password` | Confirm your new password |
| `action.add` | Add |
| `action.edit` | Edit |
| `action.delete` | Delete |
| `action.cancel` | Cancel |
| `action.confirm` | Confirm |
| `action.close` | Close |
| `action.save` | Save |
| `action.sign_in` | Sign in |
| `action.sign_out` | Sign out |
| `action.add_user` | Add user |
| `action.edit_user` | Edit user |
| `action.add_item` | Add item |
| `action.clear_selection` | Clear selection |
| `action.select_page` | Select page |
| `action.unselect_page` | Unselect page |
| `action.delete_file` | Delete file |
| `message.delete_confirm` | Are you sure you want to delete this item ? |
| `message.leave_empty_to_keep_current_password` | Leave empty to keep current password. |
| `message.item_updated` | Item updated. |
| `message.item_deleted` | Item deleted. |
| `message.account_updated` | Your account has been updated. |
| `message.password_resetted` | Your password has been resetted. |
| `message.0` |  |
| `user.anonymous` | Anonymous |
| `user.unauthenticated` | Unauthenticated |
| `login.title` | Login to your account |
| `login.forget_password` | Forgot your password ? |
| `login.sign_in` | Sign in |
| `password_resetting.request.title` | Reset your password |
| `password_resetting.request.text` | Enter the email address associated to your account. We will send yo... |
| `password_resetting.request.submit` | Reset your password |
| `password_resetting.error.title` | This link has expired. |
| `password_resetting.error.cause1` | It has been more than 24 hours since you requested a password reset... |
| `password_resetting.error.cause2` | If you have made more than one request, only the last email will be... |
| `password_resetting.success.title` | Check your emails |
| `password_resetting.success.text` | If an account matching your email exists, then an email was just se... |
| `password_resetting.reset` | Reset your password |
| `password_resetting.back_login_link` | Forget it, send me back to the sign in screen. |
| `password_resetting.back_login` | Back to sign in screen |
| `password_resetting.email.subject` | Update your password |
| `password_resetting.email.body` | Hello, A request to reset password of %name% has been made on back-... |
| `notification.empty` | You have no notifications. |
| `datatable.no_item_selected` | No item selected. |
| `datatable.one_item_selected` | 1 item selected. |
| `datatable.many_item_selected` | {c} items selected. |
| `datatable.error.load` | An error occurred while loading data. |
| `autocomplete.loading_more` | Loading more... |
| `autocomplete.no_results` | No results found. |
| `toast.error401` | You are not authenticated. |
| `toast.error403` | You are not authorized to perform this action. |
| `toast.error404` | Unable to contact server. |
| `toast.error` | An error occurred. |

Used by JavaScript: `action.add`, `action.cancel`, `action.confirm`, `autocomplete.loading_more`,
`autocomplete.no_results`, `datatable.error.load`, `datatable.no_item_selected`,
`datatable.one_item_selected`, `datatable.many_item_selected`, `toast.error`, `toast.error401`,
`toast.error403`, `toast.error404`.
