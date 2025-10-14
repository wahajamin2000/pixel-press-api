<?php

namespace App\Helpers;

class AutomationHelper
{
    /**
     * Get the icon class for a given action type
     *
     * @param string $actionType
     * @return string
     */
    public static function getActionIcon($actionType)
    {
        $icons = [
            'webhook' => 'globe',
            'feed_data_to_ai' => 'cpu',
            'summarize_conversation' => 'file-text',
            'get_last_user_message' => 'user',
            'get_last_ai_message' => 'bot',
            'continue_conversation' => 'arrow-right'
        ];

        return $icons[$actionType] ?? 'activity';
    }

    /**
     * Get the human-readable label for a given action type
     *
     * @param string $actionType
     * @return string
     */
    public static function getActionLabel($actionType)
    {
        $labels = [
            'webhook' => 'Send Webhook',
            'feed_data_to_ai' => 'Feed Data to AI',
            'summarize_conversation' => 'Summarize Conversation',
            'get_last_user_message' => 'Get Last User Message',
            'get_last_ai_message' => 'Get Last AI Message',
            'continue_conversation' => 'Continue Conversation'
        ];

        return $labels[$actionType] ?? ucfirst(str_replace('_', ' ', $actionType));
    }

    /**
     * Get the color class for a given action type
     *
     * @param string $actionType
     * @return string
     */
    public static function getActionColor($actionType)
    {
        $colors = [
            'webhook' => 'success',
            'feed_data_to_ai' => 'purple',
            'summarize_conversation' => 'info',
            'get_last_user_message' => 'primary',
            'get_last_ai_message' => 'warning',
            'continue_conversation' => 'secondary'
        ];

        return $colors[$actionType] ?? 'primary';
    }

    /**
     * Get all available action types with their metadata
     *
     * @return array
     */
    public static function getActionTypes()
    {
        return [
            'webhook' => [
                'label' => 'Send Webhook',
                'icon' => 'globe',
                'color' => 'success',
                'description' => 'Send HTTP request to external API'
            ],
            'feed_data_to_ai' => [
                'label' => 'Feed Data to AI',
                'icon' => 'cpu',
                'color' => 'purple',
                'description' => 'Process data using AI models'
            ],
            'summarize_conversation' => [
                'label' => 'Summarize Conversation',
                'icon' => 'file-text',
                'color' => 'info',
                'description' => 'Generate conversation summary'
            ],
            'get_last_user_message' => [
                'label' => 'Get Last User Message',
                'icon' => 'user',
                'color' => 'primary',
                'description' => 'Retrieve the most recent user message'
            ],
            'get_last_ai_message' => [
                'label' => 'Get Last AI Message',
                'icon' => 'bot',
                'color' => 'warning',
                'description' => 'Retrieve the most recent AI response'
            ],
            'continue_conversation' => [
                'label' => 'Continue Conversation',
                'icon' => 'arrow-right',
                'color' => 'secondary',
                'description' => 'Continue the conversation flow'
            ]
        ];
    }

    /**
     * Format automation status for display
     *
     * @param bool $enabled
     * @return array
     */
    public static function formatStatus($enabled)
    {
        return [
            'text' => $enabled ? 'Active' : 'Disabled',
            'class' => $enabled ? 'success' : 'secondary',
            'icon' => $enabled ? 'check-circle' : 'pause-circle'
        ];
    }

    /**
     * Format execution count for display
     *
     * @param int $count
     * @return string
     */
    public static function formatExecutionCount($count)
    {
        if ($count >= 1000000) {
            return number_format($count / 1000000, 1) . 'M';
        } elseif ($count >= 1000) {
            return number_format($count / 1000, 1) . 'K';
        }

        return number_format($count);
    }

    /**
     * Format response time for display
     *
     * @param int $milliseconds
     * @return string
     */
    public static function formatResponseTime($milliseconds)
    {
        if ($milliseconds >= 1000) {
            return number_format($milliseconds / 1000, 1) . 's';
        }

        return number_format($milliseconds) . 'ms';
    }

    /**
     * Get placeholder categories with their placeholders
     *
     * @return array
     */
    public static function getPlaceholderCategories()
    {
        return [
            'conversation' => [
                'title' => 'Conversation Data',
                'color' => 'primary',
                'placeholders' => [
                    'last_user_message' => 'The most recent message from the user',
                    'last_ai_message' => 'The most recent AI response',
                    'conversation_summary' => 'Summary of the conversation',
                    'conversation_id' => 'Unique identifier for the conversation'
                ]
            ],
            'automation' => [
                'title' => 'Automation Context',
                'color' => 'warning',
                'placeholders' => [
                    'current_trigger' => 'The trigger that activated this automation',
                    'automation_id' => 'ID of the current automation',
                    'assistant_id' => 'ID of the assistant (if assigned)',
                    'execution_timestamp' => 'When the automation was triggered'
                ]
            ],
            'system' => [
                'title' => 'System Values',
                'color' => 'success',
                'placeholders' => [
                    'token' => 'Your API authentication token',
                    'api_key' => 'Your API key',
                    'base_url' => 'Application base URL',
                    'timestamp' => 'Current timestamp'
                ]
            ],
            'dynamic' => [
                'title' => 'Dynamic Data',
                'color' => 'info',
                'placeholders' => [
                    'webhook_response' => 'Response from previous webhook action',
                    'webhook_response.data' => 'Data object from webhook response',
                    'source_data' => 'Data from selected AI source'
                ]
            ]
        ];
    }
}
