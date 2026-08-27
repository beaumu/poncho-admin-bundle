<?php

return [
    'label' => [
        'yes' => 'Yes',
        'no' => 'No',
        'name' => 'Name',
        'email' => 'Email',
        'created_at' => 'Created at',
        'active' => 'Active',
        'firstname' => 'Firstname',
        'lastname' => 'Lastname',
        'password' => 'Password',
        'search...' => 'Search...',
        'my_account' => 'My Account',
        'notifications' => 'Notifications',
        'enter_your_password' => 'Enter your password',
        'enter_your_new_password' => 'Enter your new password',
        'welcome' => 'Welcome',
        'enter_your_email' => 'Enter your email',
        'newpassword' => 'New password',
        'password_confirm' => 'Confirm password',
        'confirm_your_new_password' => 'Confirm your new password',
    ],
    'action' => [
        'add' => 'Add',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'cancel' => 'Cancel',
        'confirm' => 'Confirm',
        'close' => 'Close',
        'save' => 'Save',
        'sign_in' => 'Sign in',
        'sign_out' => 'Sign out',
        'add_user' => 'Add user',
        'edit_user' => 'Edit user',
        'add_item' => 'Add item',
        'clear_selection' => 'Clear selection',
        'select_page' => 'Select page',
        'unselect_page' => 'Unselect page',
        'delete_file' => 'Delete file',
    ],
    'message' => [
        'delete_confirm' => 'Are you sure you want to delete this item ?',
        'leave_empty_to_keep_current_password' => 'Leave empty to keep current password.',
        'item_updated' => 'Item updated.',
        'item_deleted' => 'Item deleted.',
        'account_updated' => 'Your account has been updated.',
        'password_resetted' => 'Your password has been resetted.',
        ''
    ],
    'user' => [
        'anonymous' => 'Anonymous',
        'unauthenticated' => 'Unauthenticated',
    ],
    'login' => [
        'title' => 'Login to your account',
        'forget_password' => 'Forgot your password ?',
        'sign_in' => 'Sign in',
    ],
    'password_resetting' => [
        'request' => [
            'title' => 'Reset your password',
            'text' => 'Enter the email address associated to your account. We will send your password recovery procedure to your mailbox.',
            'submit' => 'Reset your password',
        ],
        'error' => [
            'title' => 'This link has expired.',
            'cause1' => 'It has been more than 24 hours since you requested a password reset. You can do a new <a href="%url%" class="text-primary">password request</a>.',
            'cause2' => 'If you have made more than one request, only the last email will be valid.'
        ],
        'success' => [
            'title' => 'Check your emails',
            'text' => <<<HTML
<p>If an account matching your email exists, then an email was just sent that contains a link that you can use to reset your password.</p>
<p>If you don't receive an email please check your spam folder or try again.</p>
HTML
        ],
        'reset' => 'Reset your password',
        'back_login_link' => 'Forget it, <a href="%url%">send me back</a> to the sign in screen.',
        'back_login' => 'Back to sign in screen',
        'email' => [
            'subject' => 'Update your password',
            'body' => <<<EMAIL
<p>Hello,</p>
<p>A request to reset password of <b>%name%</b> has been made on back-office %app_name%.</p>
<p>Click <a href="%reset_url%">here</a> to define a new password.</p>
<p>Ignore this e-mail if you are not the initiator.</p>
<p>Sincerely</p>
EMAIL
        ]
    ],
    'notification' => [
        'empty' => 'You have no notifications.',
    ],
    'datatable' => [
        'no_item_selected' => 'No item selected.',
        'one_item_selected' => '1 item selected.',
        'many_item_selected' => '{c} items selected.',
        'error.load' => 'An error occurred while loading data.',
    ],
    'autocomplete' => [
        'loading_more' => 'Loading more...',
        'no_results' => 'No results found.',
    ],
    'toast' => [
        'error401' => 'You are not authenticated.',
        'error403' => 'You are not authorized to perform this action.',
        'error404' => 'Unable to contact server.',
        'error' => 'An error occurred.',
    ]
];