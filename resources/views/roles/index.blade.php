@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Manage Roles')

@section('content')
<h4>Manage Roles</h4>

<div class="card">
    <div class="card-header">Manage Roles</div>
    <div class="card-body">
        @can('create-role')
            <a href="{{ route('roles.create') }}" class="btn btn-success btn-sm my-2">Add New Role</a>
        @endcan
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                <th scope="col">S#</th>
                <th scope="col">Name</th>
                <th scope="col" style="width: 250px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($roles as $role)
                <tr>
                    <th scope="row">{{ $loop->iteration }}</th>
                    <td>{{ $role->name }}</td>
                    <td>
                        <form action="{{ route('roles.destroy', $role->id) }}" method="post">
                            @csrf
                            @method('DELETE')

                            <a href="{{ route('roles.show', $role->id) }}" class="btn btn-warning btn-sm">Show</a>

                            @if ($role->name!='Super Admin')
                                @can('edit-role')
                                    <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-primary btn-sm">Edit</a>
                                @endcan

                                @can('delete-role')
                                    @if ($role->name!=Auth::user()->hasRole($role->name))
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Do you want to delete this role?');">Delete</button>
                                    @endif
                                @endcan
                            @endif

                        </form>
                    </td>
                </tr>
                @empty
                    <td colspan="3">
                        <span class="text-danger">
                            <strong>No Role Found!</strong>
                        </span>
                    </td>
                @endforelse
            </tbody>
        </table>

        {{ $roles->links() }}

    </div>
</div>
@endsection
