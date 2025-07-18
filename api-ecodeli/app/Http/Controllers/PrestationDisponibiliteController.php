<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\ServiceAvailability;

class PrestationDisponibiliteController extends Controller
{
    public function index($serviceId)
    {
        $user = Session::get('user');
        if (!$user) {
            return response()->json(['error' => 'Non connecté'], 401);
        }

        $service = \App\Models\Service::find($serviceId);
        $prix = $service ? $service->price : null;

        $dispos = \App\Models\ServiceAvailability::where('offered_service_id', $serviceId)
            ->orderBy('date')
            ->orderBy('start_time')
            ->get(['availability_id', 'date', 'start_time', 'end_time']);

        // On ajoute le prix à chaque créneau pour le front
        $dispos = $dispos->map(function($d) use ($prix) {
            return array_merge($d->toArray(), ['price' => $prix]);
        });

        return response()->json($dispos);
    }

    public function store(Request $request, $serviceId)
    {
        $user = Session::get('user');
        if (!$user) {
            return response()->json(['error' => 'Non connecté'], 401);
        }

        $request->validate([
            'date'        => 'required|date|after_or_equal:today',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
        ]);

        // Vérifier s'il y a un chevauchement de créneau
        $conflict = ServiceAvailability::where('offered_service_id', $serviceId)
            ->where('date', $request->date)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                      ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                      ->orWhere(function ($q) use ($request) {
                          $q->where('start_time', '<', $request->start_time)
                            ->where('end_time', '>', $request->end_time);
                      });
            })
            ->exists();

        if ($conflict) {
            return response()->json([
                'error' => 'Ce créneau se chevauche avec un autre.',
            ], 409);
        }

        ServiceAvailability::create([
            'offered_service_id' => $serviceId,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return response()->json(['message' => 'Créneau ajouté avec succès.']);
    }

    public function destroy($availabilityId)
    {
        $user = Session::get('user');
        if (!$user) {
            return response()->json(['error' => 'Non connecté'], 401);
        }

        $dispo = ServiceAvailability::where('availability_id', $availabilityId)->first();

        if (!$dispo) {
            return response()->json(['error' => 'Créneau introuvable.'], 404);
        }

        $dispo->delete();

        return response()->json(['message' => 'Créneau supprimé avec succès.']);
    }
}
