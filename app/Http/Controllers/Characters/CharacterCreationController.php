<?php

namespace App\Http\Controllers\Characters;

use App\Characters\Actions\CreateCharacter;
use App\Characters\Exceptions\CharacterAccountMissing;
use App\Characters\Exceptions\CharacterBindingNotReady;
use App\Characters\Exceptions\CharacterCreationUnavailable;
use App\Characters\Exceptions\CharacterInputInvalid;
use App\Characters\Exceptions\CharacterLimitReached;
use App\Characters\Exceptions\CharacterNameConflict;
use App\Characters\Exceptions\CharacterNameInvalid;
use App\Http\Requests\Characters\CreateCharacterRequest;
use App\Identity\Models\Identity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

final class CharacterCreationController
{
    public function create(): View
    {
        return view('characters.create');
    }

    public function store(CreateCharacterRequest $request, CreateCharacter $createCharacter): RedirectResponse
    {
        $identity = $request->user();

        abort_unless($identity instanceof Identity, 403);

        try {
            $result = $createCharacter->execute(
                $identity,
                $request->characterName(),
                $request->vocation(),
                $request->sex(),
            );
        } catch (CharacterNameInvalid $exception) {
            return back()->withInput()->withErrors(['name' => $exception->getMessage()]);
        } catch (CharacterInputInvalid) {
            return back()->withInput()->withErrors(['character' => 'The character creation input is invalid.']);
        } catch (CharacterBindingNotReady) {
            return back()->withInput()->withErrors(['character' => 'Your game account is not ready for character creation.']);
        } catch (CharacterNameConflict) {
            return back()->withInput()->withErrors(['name' => 'That character name is not available.']);
        } catch (CharacterLimitReached) {
            return back()->withInput()->withErrors(['character' => 'This account already has the maximum number of active characters.']);
        } catch (CharacterAccountMissing) {
            return back()->withInput()->withErrors(['character' => 'Your bound game account is unavailable.']);
        } catch (CharacterCreationUnavailable) {
            return back()->withInput()->withErrors(['character' => 'Character creation is temporarily unavailable.']);
        }

        $status = $result->created
            ? "Character {$result->canonicalName} created."
            : "Character {$result->canonicalName} already exists on your account.";

        return redirect()
            ->route('account.characters.create')
            ->with('status', $status);
    }
}
