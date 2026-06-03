<?php
namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\Lookups\TaxExemptUserLookupResource;
use App\Http\Resources\Lookups\UserLookupResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class TaxExemptionApiController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // CUSTOMER: Apply — upload document
    // POST /tax-exemption/apply
    // Requires: auth:sanctum
    // ─────────────────────────────────────────────────────────────
    public function apply(Request $request)
    {
        $user = auth()->user();

        if ($user->tax_exempt_status === User::TAX_EXEMPT_APPROVED) {
            return $this->response(Response::HTTP_OK,
                __('Your account is already approved for tax exemption.'), []);
        }

        $request->validate([
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'], // 5MB
        ]);

        // Delete old document if re-applying after rejection
        if ($user->tax_exempt_document && $this->isStoredTaxDocument($user->tax_exempt_document)) {
            $this->deleteTaxDocument($user->tax_exempt_document);
        }

        // Store new document using config paths (same pattern as profile picture)
        $newDocument = $this->storeTaxDocument($request);

        $user->update([
            'tax_exempt_status'           => User::TAX_EXEMPT_PENDING,
            'tax_exempt_document'         => $newDocument,
            'tax_exempt_applied_at'       => now(),
            'tax_exempt_rejection_reason' => null,
        ]);

        // TODO: Fire notification to admin here

        return $this->response(Response::HTTP_OK,
            __('Tax exemption application submitted. Admin will review your document.'),
            ['status' => $user->tax_exempt_status]
        );
    }

    // ─────────────────────────────────────────────────────────────
    // CUSTOMER: Check own status
    // GET /tax-exemption/status
    // ─────────────────────────────────────────────────────────────
    public function status()
    {
        $user = auth()->user();

        return $this->response(Response::HTTP_OK, __('Fetched Successfully'), [
            'tax_exempt_status'    => $user->tax_exempt_status,
            'is_approved'          => $user->isTaxExempt(),
            'applied_at'           => $user->tax_exempt_applied_at,
            'rejection_reason'     => $user->tax_exempt_rejection_reason,
            'document_url'         => $user->tax_exempt_document_url ?? null,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // ADMIN: List pending applications
    // GET /admin/tax-exemption/pending
    // ─────────────────────────────────────────────────────────────
    public function pending()
    {
        $users = User::taxExemptPending()
            ->select('id', 'first_name', 'last_name', 'email',
                'tax_exempt_status', 'tax_exempt_document', 'tax_exempt_applied_at')
            ->get();

        return $this->response(Response::HTTP_OK, __('Fetched Successfully'), [
            'users' => TaxExemptUserLookupResource::collection($users),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // ADMIN: Approve a user
    // POST /admin/tax-exemption/{id}/approve
    // ─────────────────────────────────────────────────────────────
    public function approve(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'tax_exempt_status'           => User::TAX_EXEMPT_APPROVED,
            'tax_exempt_reviewed_at'      => now(),
            'tax_exempt_reviewed_by'      => auth()->id(),
            'tax_exempt_rejection_reason' => null,
        ]);

        // TODO: Notify customer of approval

        return $this->response(Response::HTTP_OK,
            __('User approved for tax exemption. All their future orders will be tax-free.'), []
        );
    }

    // ─────────────────────────────────────────────────────────────
    // ADMIN: Reject a user
    // POST /admin/tax-exemption/{id}/reject
    // ─────────────────────────────────────────────────────────────
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $user = User::findOrFail($id);

        $user->update([
            'tax_exempt_status'           => User::TAX_EXEMPT_REJECTED,
            'tax_exempt_reviewed_at'      => now(),
            'tax_exempt_reviewed_by'      => auth()->id(),
            'tax_exempt_rejection_reason' => $request->reason ?? null,
        ]);

        // TODO: Notify customer of rejection + reason

        return $this->response(Response::HTTP_OK,
            __('User tax exemption application rejected.'), []
        );
    }

    // ─────────────────────────────────────────────────────────────
    // HELPERS — same pattern as ProfileApiController picture helpers
    // ─────────────────────────────────────────────────────────────

    protected function storeTaxDocument(Request $request): ?string
    {
        $file = $request->file('document');

        if (!$file) {
            return null;
        }

        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $directory = 'tax-exemption-docs';
        $file->storeAs($directory, $filename, 'public');

//        $file->storeAs(config('project.storage.store.documents.tax_exemption'), $filename, 'public');

        return  $directory . '/' . $filename;
    }

    protected function deleteTaxDocument(string $fullPath): void
    {
//        $filename    = basename($fullPath);
//        $storagePath = config('project.storage.store.documents.tax_exemption') . $filename;

        if (Storage::disk('public')->exists($fullPath)) {
            Storage::disk('public')->delete($fullPath);
        }

//        if (Storage::exists($storagePath)) {
//            Storage::delete($storagePath);
//        }
    }

    protected function isStoredTaxDocument(string $docUrl): bool
    {
//        return str_contains($docUrl, config('project.storage.retrieve.documents.tax_exemption'));
        return str_contains($docUrl, config('tax-exemption-docs/'));
    }
}
