<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAdminRegistrationRequest;
use App\Models\AdminLog;
use App\Models\Event;
use App\Models\Registration;
use App\Services\MailService;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class AdminRegistrationController extends Controller
{
    public function voirInscrits(Request $request, int $eventId)
    {
        $event = Event::findOrFail($eventId);

        $this->authorize('manage', $event);

        if ($request->query('export') === 'csv') {
            return $this->exportCsv($event);
        }

        $registrations = Registration::where('event_id', $eventId)
            ->where(function ($q) {
                $q->whereNull('payment_status')->orWhere('payment_status', '!=', 'expired');
            })
            ->orderByDesc('registered_at')
            ->get();

        return view('admin.voir-inscrits', compact('event', 'registrations'));
    }

    public function modifier(int $eventId, int $registrationId)
    {
        $event = Event::findOrFail($eventId);
        $registration = Registration::where('event_id', $event->id)->findOrFail($registrationId);

        $this->authorize('manage', $event);

        return view('admin.modifier-inscrit', compact('event', 'registration'));
    }

    public function update(UpdateAdminRegistrationRequest $request, int $eventId, int $registrationId)
    {
        $event = Event::findOrFail($eventId);
        $registration = Registration::where('event_id', $event->id)->findOrFail($registrationId);

        $this->authorize('manage', $event);

        if ($request->query('action') === 'supprimer') {
            return $this->delete($registration, $event);
        }

        $oldNb = $registration->nb_participants;
        $newNb = $request->integer('nb_participants');

        DB::transaction(function () use ($request, $registration, $event, $oldNb, $newNb, &$refundInfo) {
            $registration->update([
                'firstname' => $request->input('firstname'),
                'lastname' => $request->input('lastname'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'nb_participants' => $newNb,
            ]);

            $refundInfo = null;

            if ($newNb < $oldNb && $registration->payment_status === 'paid') {
                $refundAmount = StripeService::calculateRefundAmount(
                    $event->price_amount, $oldNb, $newNb
                );
                if ($refundAmount > 0) {
                    StripeService::refundPayment(
                        $registration->payment_intent_id,
                        $refundAmount
                    );
                    $refundedAmount = number_format($refundAmount / 100, 2, ',', ' ').' €';
                    $refundInfo = "Remboursement de {$refundedAmount} initié (sous 5 à 10 jours ouvrés).";
                }
            }
        });

        MailService::sendModificationConfirmation($registration, $event, $oldNb, $refundInfo);

        AdminLog::create([
            'user_id' => Auth::id(),
            'action' => 'Modification inscription',
            'details' => "Inscription #{$registration->id} de {$registration->firstname} {$registration->lastname} (places: {$oldNb} → {$newNb})",
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('admin.inscriptions.lister', $eventId)
            ->with('success', 'Inscription modifiée avec succès.');
    }

    private function delete(Registration $registration, Event $event)
    {
        $refunded = false;

        if ($registration->payment_status === 'paid' && $registration->payment_intent_id) {
            try {
                StripeService::refundPayment($registration->payment_intent_id);
                $refunded = true;
            } catch (\Exception $e) {
                Log::error('Erreur remboursement suppression inscription: '.$e->getMessage());
            }
        }

        $registration->delete();

        MailService::sendCancellationConfirmation($registration, $event, $refunded, true);
        MailService::sendAdminCancellationNotification($registration, $event, $refunded);

        AdminLog::create([
            'user_id' => Auth::id(),
            'action' => 'Suppression inscription',
            'details' => "Inscription #{$registration->id} de {$registration->firstname} {$registration->lastname} supprimée",
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('admin.inscriptions.lister', $event->id)
            ->with('success', 'Inscription supprimée avec succès.');
    }

    private function exportCsv(Event $event)
    {
        $registrations = Registration::where('event_id', $event->id)
            ->where(function ($q) {
                $q->whereNull('payment_status')->orWhere('payment_status', '!=', 'expired');
            })
            ->orderByDesc('registered_at')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="inscriptions-'.$event->id.'.csv"',
        ];

        $callback = function () use ($event, $registrations) {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8

            fputcsv($output, ['', 'Inscriptions pour : '.$event->title, '']);
            fputcsv($output, ['', 'Date : '.$event->event_date->format('d/m/Y'), '']);
            fputcsv($output, ['', '', '']);
            fputcsv($output, ['Prénom', 'Nom', 'Email', 'Téléphone', 'Participants', 'Statut paiement', 'Inscrit le']);

            foreach ($registrations as $r) {
                fputcsv($output, [
                    $r->firstname,
                    $r->lastname,
                    $r->email,
                    $r->phone,
                    $r->nb_participants,
                    $r->payment_status ?? 'Gratuit',
                    $r->registered_at->format('d/m/Y H:i'),
                ]);
            }

            fclose($output);
        };

        return Response::stream($callback, 200, $headers);
    }
}
