<div>

    <p style="text-align: center;">
       <img src="{{config('app.url')}}/images/logo.png" width="200px" />
    </p>

    <p>Hi {{ $host->first_name }},</p>

    <p>

    </p>

    <p>
        You’re one step closer to game night greatness.
    </p>
    <p>
        Good news — {{ $player->first_name }} {{ $player->last_name }} has completed their TriviYa quiz! 🎉<br>
        Here are a few things to keep in mind before game time:

        <p>
            <ul>
                <li>✅Players can update their answers anytime — right up until you press Start Game.</li>
                <li>➕ You can add or remove players at any time before the game begins.</li>
                <li>🔐 All answers will be securely stored for 48 hours after the game wraps — then they’re gone!</li>
            </ul>
        </p>
        <p>

            @if (count($noAnswers) > 0)
                We're still waiting for answers from:
                <ul>
                @foreach($noAnswers as $noAnswer)
                    <li>{{ $noAnswer->user->name }}</li>
                @endforeach
                </ul>
                👉 <a href="{{config('app.url')}}/games/{{ $game->id }}">Click here </a> to send reminders, add or delete players.<br>
            @else
                👉 <a href="{{config('app.url')}}/games/{{ $game->id }}">Click here </a> to get the party started.<br>
            @endif

        </p>

            Let the countdown begin!<br>
             – The TriviYa Team
        </p>
    </p>
</div>
