<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeSource;
use App\Http\Requests\StoreKnowledgeSourceRequest;
use App\Jobs\ExtractTextJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KnowledgeSourceController extends Controller
{
    public function index()
    {
        $sources = KnowledgeSource::latest()->paginate(10);
        return view('dashboard.knowledge', compact('sources'));
    }

    public function store(StoreKnowledgeSourceRequest $request)
    {
        $file = $request->file('file');
        $tenantId = auth('tenant')->id();
        
        $path = $file->store("tenants/{$tenantId}/sources");

        $source = KnowledgeSource::create([
            'tenant_id' => $tenantId,
            'name' => $file->getClientOriginalName(),
            'type' => $file->getClientOriginalExtension(),
            'path' => $path,
            'status' => 'pending',
        ]);

        ExtractTextJob::dispatch($source);

        return back()->with('success', 'File uploaded and processing started.');
    }

    public function destroy(KnowledgeSource $knowledgeSource)
    {
        // Global scope ensures tenant isolation
        Storage::delete($knowledgeSource->path);
        $knowledgeSource->delete();
        
        return back()->with('success', 'Source deleted.');
    }
}
