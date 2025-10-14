<?php
// app/Services/Automation/Actions/ActionExecutorInterface.php
namespace App\Interfaces;

use App\Models\Modules\Automations\AutomationAction;

interface ActionExecutorInterface
{
    /**
     * Execute the action with given data
     *
     * @param AutomationAction $action
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function execute(AutomationAction $action, array $data): array;

    /**
     * Validate the action configuration
     *
     * @param array $config
     * @return array Array of validation errors (empty if valid)
     */
    public function validateConfig(array $config): array;

    /**
     * Get the expected configuration schema
     *
     * @return array
     */
    public function getConfigSchema(): array;
}
