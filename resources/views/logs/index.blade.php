@extends('layouts.master')

@section('breadcrumbs')
    <li class="breadcrumb-item active">{{ $title }}</li>
@endsection

@section('content')

    <div class="container">
        <form method="get" action="/logs" class="form-inline">
            {{ csrf_field() }}
            
            <div class="row">
                <div class="form-group">
                    <label>Title
                        <input name="title" type="checkbox">&nbsp;&nbsp;
                    </label>
                </div>
                <div class="form-group">
                    <label>Description
                        <input name="desc" type="checkbox">&nbsp;&nbsp;
                    </label>
                </div>
                <div class="form-group">
                    <label>Created at
                        <input name="created_at" type="checkbox">&nbsp;&nbsp;
                    </label>
                </div>
                <div class="form-group">
                    <label>Updated at
                        <input name="updated_at" type="checkbox">&nbsp;&nbsp;
                    </label>
                </div>
            </div>
            <div class="row">
                <div class="form-group">
                    <label>
                        <input value='' name="search" type="text" placeholder="Search..." class="form-control">
                    </label>
                </div>

                <div class="form-group">
                    <label>
                        <input type="submit" class="form-control">
                    </label>
                </div>
            </div>
        </form>
    </div>

    <div class="text-center">
        {{ $logs->total() }} results
    </div>
    
    <table class="table {{ count($logs) == 0 ? 'hide' : '' }}">

        <thead>
            <th>Id</th>
            <th>Title</th>
            <th>Description</th>
            <th>Created</th>
            <th>Updated</th>
        </thead>

        <tbody>

            @forelse($logs as $log)

                <tr>
                    <td><a href="#">{{ $log->id }}</a></td>
                    <td>{{ $log->title }}</td>
                    <td>{{ $log->description}}</td>
                    <td>{{ $log->created_at}}</td>
                    @if($log->updated_at)
                        <td>{{$log->updated_at}}</td>
                    @else
                        <td>n/a</td>
                    @endif
                </tr>

            @empty
                <div class="text-center">
                    There are no logs to show
                </div>
            @endforelse

        </tbody>

    </table>

    <div class="text-center">
            {{ $logs->links() }}
    </div>


@endsection