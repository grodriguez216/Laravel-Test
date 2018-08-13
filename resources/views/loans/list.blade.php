@extends('layouts.app')

@php
use App\Helper;
@endphp

@section('content')
<div class="container-fluid">
	<div class="row">
		<div class="col">
			<table class="table table-sm">
				<thead>
					<tr>
						<th scope="col">#</th>
						<th scope="col">Client</th>
						<th scope="col">Balance</th>
						<th scope="col">Delays</th>
						<th scope="col">Collecting</th>
						<th scope="col">Next Payment</th>
						<th scope="col">Due</th>
					</tr>
				</thead>
				<tbody>
					@foreach($loans_today as $loan)
					<tr>
						<th scope="row">{{ $loan->id }}</th>
						<td>
							<a href="http://control-parcero.co/clientes/perfil/{{ $loan->client_id }}" target="_blank">
								{{ $loan->client_id }}
							</a>
						</td>



						<td><span class="money">{{ $loan->balance }}</span></td>
						<td>{{ $loan->delays }}</td>
						<td>{{ $loan->collect_date }}</td>
						<td>{{ $loan->next_due }}</td>
						<td>{{ $loan->due_date }}</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>
</div>
@endsection


@section('scripts')
<script type="text/javascript">
	window.onload = function()
	{
		window.nicecify_money();
		toggle('loader', false );
	};
	function nicecify_money()
	{
		var items = document.getElementsByClassName('money');
		for (var i = 0; i < items.length; i++)
			items[i].innerHTML = nicecify( items[i].innerHTML );
	}
</script>
@endsection