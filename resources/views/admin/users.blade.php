@extends('layouts.app')
@section('title','Admin — Users')
@section('content')
<div class="page-head"><div><span class="eyebrow">ADMIN CONTROL CENTER</span><h1>User management.</h1></div></div>
<section class="panel glass"><div class="panel-title"><h2>Users</h2><span>● online = active session</span></div>
@foreach($users as $user)<div class="row"><b>{{ $user->name }} @if(in_array($user->id,$onlineIds,true))<small style="color:#38d39f">● online</small>@endif</b><span>{{ $user->email }} · ${{ number_format($user->cash_balance,2) }}</span><form method="POST" action="{{ route('admin.users.balance',$user) }}">@csrf<input name="amount" type="number" min=".01" step=".01" placeholder="Amount" required><button name="operation" value="add">Add</button><button name="operation" value="reduce">Reduce</button></form><form method="POST" action="{{ route('admin.users.destroy',$user) }}" onsubmit="return confirm('Delete this user permanently?')">@csrf @method('DELETE')<button type="submit">Delete</button></form></div>@endforeach
</section>
@endsection
