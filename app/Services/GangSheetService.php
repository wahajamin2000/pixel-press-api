<?php

namespace App\Services;

use App\Models\Gangsheet;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GangSheetService
{
    private string $baseUrl;
    private string $apiToken;
    private int $timeout;
    public function __construct()
    {
        $this->apiToken = config('services.gangsheet.api_token');
        $this->baseUrl = config('services.gangsheet.base_url', 'https://app.buildagangsheet.com/api/v1');
        $this->timeout = config('services.gangsheet.timeout', 60);
    }

    /**
     * Create a new design in BuildAGangSheet
     * This is called when creating an order with gangsheet items
     *
     * @param array $designData Design parameters
     * @return array|null Returns design data including design_id
     */
    public function createDesign(array $designData): ?array
    {
        try {
            Log::info('Creating gang sheet design in BuildAGangSheet', [
                'design_data' => $designData
            ]);

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->post("{$this->baseUrl}/design", $designData);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('Gang sheet design created successfully in BuildAGangSheet', [
                    'design_id' => $data['design']['id'] ?? null,
                    'status' => $data['design']['status'] ?? 'unknown'
                ]);

                // Sync to database immediately
                if (isset($data['design']['id'])) {
                    $this->syncToDatabase($data['design']['id'], $data);
                }

                return $data;
            }

            Log::error('Failed to create gang sheet design in BuildAGangSheet', [
                'status' => $response->status(),
                'response' => $response->body(),
                'design_data' => $designData
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Exception while creating gang sheet design', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'design_data' => $designData
            ]);

            return null;
        }
    }
    /**
     * Get design details from BuildAGangSheet
     * This method will try to get from database first, then API if needed
     *
     * @param string $designId
     * @param bool $forceRefresh Force refresh from API
     * @return array|null
     */
    public function getDesignDetails(string $designId, bool $forceRefresh = false): ?array
    {
        // Try to get from database first
        $gangsheet = Gangsheet::where('design_id', $designId)->first();

        // If found in database and not forcing refresh and recently synced
        if ($gangsheet && !$forceRefresh && $gangsheet->last_synced_at &&
            $gangsheet->last_synced_at->diffInHours(now()) < 1) {

            Log::info('Using cached gang sheet design details from database', [
                'design_id' => $designId,
                'last_synced' => $gangsheet->last_synced_at
            ]);

            return $this->formatGangsheetToApiResponse($gangsheet);
        }

        // Fetch from API
        try {
            Log::info('Fetching gang sheet design details from API', [
                'design_id' => $designId,
                'force_refresh' => $forceRefresh
            ]);

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->get("{$this->baseUrl}/design/{$designId}");

            if ($response->successful()) {
                $data = $response->json();

                Log::info('Gang sheet design details fetched successfully', [
                    'design_id' => $designId,
                    'status' => $data['design']['status'] ?? 'unknown',
                    'api_response_data' => $data,
                ]);

                // Update or create in database
                $this->syncToDatabase($designId, $data);

                return $data;
            }

            Log::error('Failed to fetch gang sheet design details', [
                'design_id' => $designId,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            // If API fails but we have cached data, return it
            if ($gangsheet) {
                Log::warning('Returning stale cached data due to API failure', [
                    'design_id' => $designId
                ]);
                return $this->formatGangsheetToApiResponse($gangsheet);
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Exception while fetching gang sheet design', [
                'design_id' => $designId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // If exception but we have cached data, return it
            if ($gangsheet) {
                Log::warning('Returning stale cached data due to exception', [
                    'design_id' => $designId
                ]);
                return $this->formatGangsheetToApiResponse($gangsheet);
            }

            return null;
        }
    }

    /**
     * Generate gang sheet file (finalize the design)
     *
     * @param string $designId
     * @param string|null $fileName
     * @return array|null
     */
    public function generateDesign(string $designId, ?string $fileName = null): ?array
    {
        try {
            Log::info('Generating gang sheet design', [
                'design_id' => $designId,
                'file_name' => $fileName
            ]);

            $payload = [
                'design_id' => $designId,
            ];

            if ($fileName) {
                $payload['file_name'] = $fileName;
            }

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->post("{$this->baseUrl}/design/generate", $payload);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('Gang sheet design generated successfully', [
                    'design_id' => $designId,
                    'response' => $data
                ]);

                // Update database after generation
                $this->syncToDatabase($designId, $data);

                return $data;
            }

            Log::error('Failed to generate gang sheet design', [
                'design_id' => $designId,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Exception while generating gang sheet design', [
                'design_id' => $designId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return null;
        }
    }

    /**
     * Get download URL for a gang sheet
     * First checks database, then API if needed
     *
     * @param string $designId
     * @return string|null
     */
    public function getDownloadUrl(string $designId): ?string
    {
        // Try database first
        $gangsheet = Gangsheet::where('design_id', $designId)->first();

        if ($gangsheet && $gangsheet->download_url && $gangsheet->is_completed) {
            return $gangsheet->download_url;
        }

        // Fall back to API
        $designDetails = $this->getDesignDetails($designId, true);

        if (!$designDetails || !isset($designDetails['design']['download_url'])) {
            return null;
        }

        return $designDetails['design']['download_url'];
    }

    /**
     * Validate gang sheet design ID exists
     *
     * @param string $designId
     * @return bool
     */
    public function validateDesignId(string $designId): bool
    {
        $details = $this->getDesignDetails($designId);
        return $details !== null && isset($details['design']['id']);
    }

    /**
     * Check if design is completed
     *
     * @param string $designId
     * @return bool
     */
    public function isDesignCompleted(string $designId): bool
    {
        // Check database first
        $gangsheet = Gangsheet::where('design_id', $designId)->first();

        if ($gangsheet && $gangsheet->is_completed) {
            return true;
        }

        // Verify with API
        $details = $this->getDesignDetails($designId);
        return $details !== null &&
            isset($details['design']['status']) &&
            $details['design']['status'] === 'completed';
    }

    /**
     * Sync gang sheet data to database
     *
     * @param string $designId
     * @param array $apiResponse
     * @return Gangsheet
     */
    public function syncToDatabase(string $designId, array $apiResponse): Gangsheet
    {
        return Gangsheet::createOrUpdateFromApi($designId, $apiResponse);
    }

    /**
     * Get gangsheet from database
     *
     * @param string $designId
     * @return Gangsheet|null
     */
    public function getFromDatabase(string $designId): ?Gangsheet
    {
        return Gangsheet::where('design_id', $designId)->first();
    }

    /**
     * Create gangsheet in database
     *
     * @param string $designId
     * @param array $additionalData
     * @return Gangsheet
     */
    public function createInDatabase(string $designId, array $additionalData = []): Gangsheet
    {
        return Gangsheet::create(array_merge([
            'design_id' => $designId,
            'status' => 'pending',
        ], $additionalData));
    }

    /**
     * Create gangsheet design in BuildAGangSheet API AND save to database
     * This is the main method to use when creating new gangsheet orders
     *
     * @param array $designData Design parameters for BuildAGangSheet
     * @return Gangsheet|null
     */
    public function createAndSyncGangsheet(array $designData): ?Gangsheet
    {
        // Create design in BuildAGangSheet API
        $apiResponse = $this->createDesign($designData);

        if (!$apiResponse || !isset($apiResponse['design']['id'])) {
            Log::error('Failed to create gangsheet design, API response invalid', [
                'design_data' => $designData,
                'api_response' => $apiResponse
            ]);
            return null;
        }

        $designId = $apiResponse['design']['id'];

        // Get the synced gangsheet from database
        $gangsheet = $this->getFromDatabase($designId);

        if (!$gangsheet) {
            Log::error('Gangsheet was created in API but not found in database', [
                'design_id' => $designId
            ]);
            return null;
        }

        return $gangsheet;
    }
    /**
     * Format Gangsheet model to API response format
     *
     * @param Gangsheet $gangsheet
     * @return array
     */
    private function formatGangsheetToApiResponse(Gangsheet $gangsheet): array
    {
        return [
            'design' => [
                'id' => $gangsheet->design_id,
                'name' => $gangsheet->name,
                'file_name' => $gangsheet->file_name,
                'size' => $gangsheet->size,
                'order_type' => $gangsheet->order_type,
                'quality' => $gangsheet->quality,
                'status' => $gangsheet->status,
                'download_url' => $gangsheet->download_url,
                'thumbnail_url' => $gangsheet->thumbnail_url,
                'edit_url' => $gangsheet->edit_url,
                'images' => $gangsheet->images,
                'width' => $gangsheet->width,
                'height' => $gangsheet->height,
                'image_count' => $gangsheet->image_count,
            ],
            'metadata' => $gangsheet->metadata,
        ];
    }

    /**
     * Sync pending gangsheets from database with API
     * Useful for background jobs to keep data fresh
     *
     * @param int $hours Hours since last sync
     * @return array
     */
    public function syncPendingGangsheets(int $hours = 1): array
    {
        $gangsheets = Gangsheet::needsSyncing($hours)->get();
        $synced = [];
        $failed = [];

        foreach ($gangsheets as $gangsheet) {
            try {
                $apiResponse = $this->getDesignDetails($gangsheet->design_id, true);
                if ($apiResponse) {
                    $synced[] = $gangsheet->design_id;
                } else {
                    $failed[] = $gangsheet->design_id;
                }
            } catch (\Exception $e) {
                $failed[] = $gangsheet->design_id;
                Log::error('Failed to sync gangsheet', [
                    'design_id' => $gangsheet->design_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return [
            'synced' => $synced,
            'failed' => $failed,
            'total' => $gangsheets->count(),
        ];
    }
}
