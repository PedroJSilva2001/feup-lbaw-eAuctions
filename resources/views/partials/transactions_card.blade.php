<div class="col mb-3 card-container">
    <div class="card">
        <div class="card-header text-md-start text-truncate d-flex justify-content-between">
            <h4 class="col-md-8 my-auto">
                {{ $description }}
                <a href="{{ url('/users/' . $user)}}"> {{ App\Models\User::findOrFail($user)->name }} </a>
            </h4>
            @if ($status <> 'Pending')
                <h6 class=" col-md-4 text-end my-auto"> {{ $date }}</h6>
            @endif
        </div>
        <div class="card-body d-flex justify-content-start">
            <div class="card-text row card-body-info px-2 w-100">
                <div class="col-md-8">
                    <h6 class="m-1"> Value: {{ abs($value) }} €</h6>
                    <h6 class="m-1"> Method: {{ $method }}</h6>
                </div>
                <div class="col-md-4 d-flex justify-content-end">
                    @if ($status == 'Pending' && !($isSettings))
                        <form method="POST" action="{{ route('transaction_confirm', $id) }}" class="mx-auto">
                            {{ csrf_field() }}
                            <button type="submit" class="btn enter-info" name='action' value="Accepted">Accept</button>
                            <button type="submit" class="btn enter-info" name='action' value="Declined">Decline</button>
                        </form>
                    @else

                        <h6 class="my-auto"> {{ $status }} </h6>
                    
                    @endif
                </div>
            </div>
        </div>
        
    </div>
</div>
