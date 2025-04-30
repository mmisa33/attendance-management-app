@extends('layouts.app')


@section('content')
<div class="container">

    <!-- ログアウトフォーム -->
    <form action="{{ route('admin.logout') }}" method="POST" style="margin-top: 20px;">
        @csrf
        <button type="submit" class="btn btn-danger">ログアウト</button>
    </form>
</div>
@endsection