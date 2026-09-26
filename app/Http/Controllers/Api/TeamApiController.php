<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeamResource;
use App\Http\Resources\UserResource;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TeamApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $teams = Team::with('owner')
            ->withCount('members')
            ->where(fn ($q) => $q
                ->where('owner_id', $request->user()->id)
                ->orWhereHas('members', fn ($m) => $m->where('users.id', $request->user()->id)))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return TeamResource::collection($teams)->response();
    }

    public function show(Request $request, Team $team): JsonResponse
    {
        $this->authorizeTeam($request, $team);
        $team->load(['owner', 'members'])->loadCount('members');

        return response()->json(['team' => new TeamResource($team)]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
        ]);

        $slug = Str::slug($data['name']);
        $base = $slug;
        $i = 1;
        while (Team::where('slug', $slug)->exists()) {
            $slug = $base . '-' . ++$i;
        }

        $team = Team::create([...$data, 'slug' => $slug, 'owner_id' => $request->user()->id]);
        $team->members()->syncWithoutDetaching([$request->user()->id]);
        $team->load('owner')->loadCount('members');

        return response()->json(['message' => 'Team created.', 'team' => new TeamResource($team)], 201);
    }

    public function update(Request $request, Team $team): JsonResponse
    {
        abort_unless($team->owner_id === $request->user()->id, 403, 'Only the owner can update the team.');

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
        ]);

        $team->update($data);

        return response()->json(['message' => 'Team updated.', 'team' => new TeamResource($team)]);
    }

    public function destroy(Request $request, Team $team): JsonResponse
    {
        abort_unless($team->owner_id === $request->user()->id, 403, 'Only the owner can delete the team.');
        $team->delete();

        return response()->json(['message' => 'Team deleted.']);
    }

    /**
     * Add a member to the team by email.
     */
    public function addMember(Request $request, Team $team): JsonResponse
    {
        $this->authorizeTeam($request, $team);

        $data = $request->validate(['email' => 'required|email']);
        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            return response()->json(['message' => 'No user found with that email.'], 404);
        }

        $team->members()->syncWithoutDetaching([$user->id]);
        $team->load('members');

        return response()->json([
            'message' => 'Member added.',
            'members' => UserResource::collection($team->members),
        ]);
    }

    /**
     * Remove a member from the team (owner only).
     */
    public function removeMember(Request $request, Team $team, int $userId): JsonResponse
    {
        abort_unless($team->owner_id === $request->user()->id, 403, 'Only the owner can remove members.');

        $team->members()->detach($userId);

        return response()->json(['message' => 'Member removed.']);
    }

    private function authorizeTeam(Request $request, Team $team): void
    {
        $user = $request->user();
        abort_unless(
            $team->owner_id === $user->id || $team->members()->where('users.id', $user->id)->exists(),
            403,
            'You are not a member of this team.'
        );
    }
}
