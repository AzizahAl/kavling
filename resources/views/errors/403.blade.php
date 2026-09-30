@extends('errors.layout')
@section('kode', '403')
@section('judul', 'Akses Ditolak')
@section('pesan', $exception->getMessage() ?: 'Anda tidak memiliki izin untuk membuka halaman ini.')
