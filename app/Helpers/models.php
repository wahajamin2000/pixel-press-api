<?php

/*
=> Register File in composer.json

"autoload-dev": {
    ...
    "files": [
        "app/Helpers/helpers.php",
    ]
},

=> Run command: composer dump-autoload
*/


use App\Models\User;

if (!function_exists('is_level')) {
    function is_level($level, $user = null)
    {
        if (!isset($level) || empty($level)) return false;
        $isArray = is_array($level);
        try {
            if ($user instanceof User) {}
            elseif (is_numeric($user)) { $user = User::find($user); if (!isset($user)) return false; }
            elseif (auth_check()) { $user = auth_user(); }

            if (!$isArray) return $user->isLevel($level) ? true : false;
            foreach ($level as $value) { if ($user->isLevel($value)) return true; }
            return false;
        } catch (Exception $exception) {
            return false;
        }
    }
}
if (!function_exists('is_not_level')) {
    function is_not_level($level, $user = null)
    {
        if (!isset($level) || empty($level)) return true;
        $isArray = is_array($level);
        try {
            if ($user instanceof User) {

            } elseif (is_numeric($user)) {
                $user = User::find($user);
                if (!isset($user)) return true;
            } elseif (auth_check()) {
                $user = auth_user();
            }

            if (!$isArray) return $user->isLevel($level) ? false : true;
            foreach ($level as $value) {
                if ($user->isLevel($value)) return false;
            }
            return true;

        } catch (Exception $exception) {
            return true;
        }
    }
}


if (!function_exists('auth_check')) {
    function auth_check()
    {
        try {
            return auth()->check();
        } catch (Exception $exception) {
            return false;
        }
    }
}
if (!function_exists('auth_user')) {
    function auth_user($guard = null)
    {
        try {
            return auth($guard)->user();
        } catch (Exception $exception) {
            return null;
        }
    }
}
if (!function_exists('auth_id')) {
    function auth_id()
    {
        try {
            return auth()->id();
        } catch (Exception $exception) {
            return null;
        }
    }
}
if (!function_exists('auth_name')) {
    function auth_name()
    {
        try {
            $user = auth_user(); // or auth()->user()

            if (!$user) {
                return null;
            }

            // Prefer first_name + last_name if available
            if (!empty($user->first_name) || !empty($user->last_name)) {
                return sprintf('%s %s', auth_user()->first_name, auth_user()->last_name);
            }

            // Fallback to 'name' column
            if (!empty($user->name)) {
                return $user->name;
            }

            return null;
        } catch (Exception $exception) {
            return null;
        }
    }
}

if (!function_exists('auth_email')) {
    function auth_email()
    {
        try {
            return auth_user()->email;
        } catch (Exception $exception) {
            return null;
        }
    }
}


if (!function_exists('is_me')) {
    function is_me($user)
    {
        try {
            if (!auth_check()) return false;

            if ($user instanceof User) {
            } elseif (is_numeric($user)) {
                $user = User::find($user);
                if (!isset($user)) return false;
            }

            return auth_id() == $user->id ? true : false;

        } catch (Exception $exception) {
            return false;
        }
    }
}


if (!function_exists('is_not_me')) {
    function is_not_me($user)
    {
        try {
            if (!auth_check()) return true;

            if ($user instanceof User) {
            } elseif (is_numeric($user)) {
                $user = User::find($user);
                if (!isset($user)) return true;
            }
            return auth_id() == $user->id ? false : true;

        } catch (Exception $exception) {
            return true;
        }
    }
}

if (!function_exists('is_system_user')) {
    function is_system_user($user = null)
    {
        try {
            if ($user instanceof User) {

            } elseif (is_numeric($user)) {
                $user = User::find($user);
                if (!isset($user)) return false;
            } elseif (auth_check()) {
                $user = auth_user();
            }

//            return is_business($user) || is_admin($user) || is_personal_trainer($user) || is_client($user) ? true : false;
            return is_business($user) || is_admin($user) ? true : false;

        } catch (Exception $exception) {
            return false;
        }
    }
}

if (!function_exists('is_super_admin')) {
    function is_super_admin($user = null)
    {
        try {
            if ($user instanceof User) {

            } elseif (is_numeric($user)) {
                $user = User::find($user);
                if (!isset($user)) return false;
            } elseif (auth_check()) {
                $user = auth_user();
            }

            return $user->isLevel(User::LEVEL_SUPER_ADMIN) ? true : false;

        } catch (Exception $exception) {
            return false;
        }
    }
}

if (!function_exists('is_not_super_admin')) {
    function is_not_super_admin($user = null)
    {
        try {
            if ($user instanceof User) {
            } elseif (is_numeric($user)) {
                $user = User::find($user);
                if (!isset($user)) return false;
            } elseif (auth_check()) {
                $user = auth_user();
            }

            return $user->isLevel(User::LEVEL_SUPER_ADMIN) ? false : true;

        } catch (Exception $exception) {
            return false;
        }
    }
}

if (!function_exists('is_admin')) {
    function is_admin($user = null)
    {
        try {
            if ($user instanceof User) {

            } elseif (is_numeric($user)) {
                $user = User::find($user);
                if (!isset($user)) return false;
            } elseif (auth_check()) {
                $user = auth_user();
            }

            return $user->isLevel(User::LEVEL_ADMIN) ? true : false;

        } catch (Exception $exception) {
            return false;
        }
    }
}

if (!function_exists('is_not_admin')) {
    function is_not_admin($user = null)
    {
        try {
            if ($user instanceof User) {

            } elseif (is_numeric($user)) {
                $user = User::find($user);
                if (!isset($user)) return true;
            } elseif (auth_check()) {
                $user = auth_user();
            }

            return $user->isLevel(User::LEVEL_ADMIN) ? false : true;

        } catch (Exception $exception) {
            return true;
        }
    }
}

if (!function_exists('is_manager')) {
    function is_manager($user = null)
    {
        try {
            if ($user instanceof User) {

            } elseif (is_numeric($user)) {
                $user = User::find($user);
                if (!isset($user)) return false;
            }
            if (auth_check()) {
                $user = auth_user();
            }

            return $user->isLevel(User::LEVEL_MANAGER) ? true : false;

        } catch (Exception $exception) {
            return false;
        }
    }
}

if (!function_exists('is_not_manager')) {
    function is_not_manager($user = null)
    {
        try {
            if ($user instanceof User) {
            } elseif (is_numeric($user)) {
                $user = User::find($user);
                if (!isset($user)) return false;
            } elseif (auth_check()) {
                $user = auth_user();
            }

            return $user->isLevel(User::LEVEL_MANAGER) ? false : true;

        } catch (Exception $exception) {
            return false;
        }
    }
}

if (!function_exists('is_customer')) {
    function is_customer($user = null)
    {
        try {
            if ($user instanceof User) {

            } elseif (is_numeric($user)) {
                $user = User::find($user);
                if (!isset($user)) return false;
            }
            if (auth_check()) {
                $user = auth_user();
            }

            return $user->isLevel(User::LEVEL_CUSTOMER) ? true : false;

        } catch (Exception $exception) {
            return false;
        }
    }
}

if (!function_exists('is_not_customer')) {
    function is_not_customer($user = null)
    {
        try {
            if ($user instanceof User) {
            } elseif (is_numeric($user)) {
                $user = User::find($user);
                if (!isset($user)) return false;
            } elseif (auth_check()) {
                $user = auth_user();
            }

            return $user->isLevel(User::LEVEL_CUSTOMER) ? false : true;

        } catch (Exception $exception) {
            return false;
        }
    }
}


if (!function_exists('genders')) {
    function genders()
    {
        try {
            return User::GENDERS;
        } catch (Exception $exception) {
            return [];
        }
    }
}

if (!function_exists('user_roles')) {
    function user_roles($formType = User::KEY_FORM_TYPE_CREATE, $user = null)
    {
        try {

            $userLevel = null;
            if ($user instanceof User) {
            } elseif (is_numeric($user)) {
                $user = User::find($user);
                if (!isset($user)) return [];
            } elseif (auth_check()) {
                $user = auth_user();
            }

            $userLevel = isset($user) ? $user->level : User::LEVEL_SUPER_ADMIN;

            foreach (User::ROLES as $level => $role) {
                if ($formType === User::KEY_FORM_TYPE_CREATE) {
                    if ($level > $userLevel) $roles[$level] = $role;
                } elseif ($formType === User::KEY_FORM_TYPE_EDIT) {
                    if (is_me($user)) {
                        if ($level >= $userLevel) $roles[$level] = $role;
                    } else {
                        if ($level > $userLevel) $roles[$level] = $role;
                    }
                }
            }
            return $roles ?? [];
        } catch (Exception $exception) {
            return [];
        }
    }
}

if (!function_exists('user_statuses')) {
    function user_statuses()
    {
        return \App\Enums\StatusEnum::options();
    }
}

if (!function_exists('getActionIcon')) {
    // Helper functions for the view
    function getActionIcon($actionType) {
        $icons = [
            'webhook' => 'globe',
            'feed_data_to_ai' => 'cpu',
            'summarize_conversation' => 'file-text',
            'get_last_user_message' => 'user',
            'get_last_ai_message' => 'reddit',
            'continue_conversation' => 'arrow-right',
            'custom_gpt_prompt' => 'arrow-right',
        ];
        return $icons[$actionType] ?? 'activity';
    }
}

if (!function_exists('getActionLabel')) {
    function getActionLabel($actionType) {
        $labels = [
            'webhook' => 'Send Webhook',
            'feed_data_to_ai' => 'Feed Data to AI',
            'summarize_conversation' => 'Summarize Conversation',
            'get_last_user_message' => 'Get Last User Message',
            'get_last_ai_message' => 'Get Last AI Message',
            'continue_conversation' => 'Continue Conversation',
            'custom_gpt_prompt' => 'Custom GPT Prompt',
        ];
        return $labels[$actionType] ?? ucfirst(str_replace('_', ' ', $actionType));
    }
}
