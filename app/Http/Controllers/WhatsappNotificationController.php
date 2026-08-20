<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\WhatsappContactImport;
use App\Models\WhatsappContact;
use App\Models\WhatsappNotification;
use App\Models\WhatsappNotificationRecipient;

class WhatsappNotificationController extends Controller
{
    /* ================= LIST + SCHEDULE FORM ================= */
    public function index()
    {
        $contacts = WhatsappContact::orderBy('name')->get();

        $notifications = WhatsappNotification::withCount('recipients')
            ->orderBy('scheduled_at', 'desc')
            ->paginate(20);

        $statusCounts = WhatsappNotification::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $templateName = config('services.whatsapp.template_name');
        $templateLanguage = config('services.whatsapp.template_language');
        $paramLabels = config('services.whatsapp.template_param_labels', []);

        return view('whatsapp.index', compact(
            'contacts', 'notifications', 'statusCounts',
            'templateName', 'templateLanguage', 'paramLabels'
        ));
    }

    /* ================= IMPORT EXCEL ================= */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        Excel::import(new WhatsappContactImport, $request->file('file'));

        return redirect()
            ->route('whatsapp.index')
            ->with('success', 'Contacts imported successfully!');
    }

    /* ================= SCHEDULE A NOTIFICATION ================= */
    public function store(Request $request)
    {
        $paramLabels = config('services.whatsapp.template_param_labels', []);

        $request->validate([
            'target_type' => 'required|in:all,selected',
            'contact_ids' => 'required_if:target_type,selected|array',
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'required',
            'params' => 'array',
            'header_image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $params = array_values($request->input('params', []));

        foreach ($paramLabels as $index => $label) {
            if (!isset($params[$index]) || $params[$index] === '') {
                return back()->withInput()->withErrors(["params.$index" => "\"$label\" is required."]);
            }
        }

        $scheduledAt = $request->scheduled_date . ' ' . $request->scheduled_time;

        $headerImagePath = null;
        if ($request->hasFile('header_image')) {
            $headerImagePath = $request->file('header_image')->store('whatsapp/headers', 'public');
        }

        $notification = WhatsappNotification::create([
            'template_name' => config('services.whatsapp.template_name'),
            'template_language' => config('services.whatsapp.template_language'),
            'template_params' => $params,
            'header_image_path' => $headerImagePath,
            'target_type' => $request->target_type,
            'scheduled_at' => $scheduledAt,
            'status' => 'pending',
        ]);

        $contactIds = $request->target_type === 'all'
            ? WhatsappContact::pluck('id')
            : collect($request->contact_ids);

        foreach ($contactIds as $contactId) {
            WhatsappNotificationRecipient::create([
                'whatsapp_notification_id' => $notification->id,
                'whatsapp_contact_id' => $contactId,
                'status' => 'pending',
            ]);
        }

        return redirect()
            ->route('whatsapp.index')
            ->with('success', 'Notification scheduled successfully!');
    }

    /* ================= DELETE CONTACT ================= */
    public function deleteContact(Request $request)
    {
        $request->validate(['id' => 'required|integer']);

        WhatsappContact::where('id', $request->id)->delete();

        return redirect()
            ->route('whatsapp.index')
            ->with('success', 'Contact deleted successfully!');
    }
}
