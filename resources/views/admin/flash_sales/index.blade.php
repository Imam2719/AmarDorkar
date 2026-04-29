@extends('layouts.admin')
@section('title', 'Flash Sale Management')
@section('page-title', 'Flash Sale Management')

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-3"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="mb-3 d-flex justify-content-end">
    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#addFlashSaleModal">
        <i class="fas fa-bolt me-2"></i>{{ app()->getLocale()==='bn' ? 'নতুন ফ্ল্যাশ সেল' : 'New Flash Sale' }}
    </button>
</div>

<div class="card animate-fadeInUp">
    <div class="card-header">
        <h5 class="card-title"><i class="fas fa-bolt me-2 text-warning"></i>{{ app()->getLocale()==='bn' ? 'সকল ফ্ল্যাশ সেল' : 'All Flash Sales' }}</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>{{ app()->getLocale()==='bn' ? 'শিরোনাম' : 'Title' }}</th>
                        <th>{{ app()->getLocale()==='bn' ? 'শুরু' : 'Start' }}</th>
                        <th>{{ app()->getLocale()==='bn' ? 'শেষ' : 'End' }}</th>
                        <th>{{ app()->getLocale()==='bn' ? 'পণ্য সংখ্যা' : 'Products' }}</th>
                        <th>{{ app()->getLocale()==='bn' ? 'স্ট্যাটাস' : 'Status' }}</th>
                        <th>{{ app()->getLocale()==='bn' ? 'কার্যক্রম' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($flashSales as $fs)
                <tr>
                    <td>
                        <strong>{{ $fs->title_en }}</strong><br>
                        <small class="text-muted bengali-text">{{ $fs->title_bn }}</small>
                    </td>
                    <td>{{ $fs->start_time->format('d M Y, h:i A') }}</td>
                    <td>{{ $fs->end_time->format('d M Y, h:i A') }}</td>
                    <td>
                        <span class="badge bg-primary">{{ $fs->products->count() }} {{ app()->getLocale()==='bn' ? 'টি' : 'items' }}</span>
                    </td>
                    <td>
                        @if($fs->is_active && $fs->end_time > now() && $fs->start_time <= now())
                            <span class="badge bg-success"><i class="fas fa-bolt me-1"></i>{{ app()->getLocale()==='bn' ? 'চলছে' : 'LIVE' }}</span>
                        @elseif($fs->is_active)
                            <span class="badge bg-warning text-dark">{{ app()->getLocale()==='bn' ? 'সক্রিয়' : 'Active' }}</span>
                        @else
                            <span class="badge bg-secondary">{{ app()->getLocale()==='bn' ? 'নিষ্ক্রিয়' : 'Inactive' }}</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('admin.flash-sales.show', $fs) }}" class="btn btn-sm btn-outline-primary" title="Manage Products">
                                <i class="fas fa-cog"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.flash-sales.toggle', $fs) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $fs->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }}" title="Toggle">
                                    <i class="fas {{ $fs->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.flash-sales.destroy', $fs) }}" class="d-inline" onsubmit="return confirm('Delete this Flash Sale?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-4 text-muted">{{ app()->getLocale()==='bn' ? 'কোন ফ্ল্যাশ সেল নেই' : 'No Flash Sales found' }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($flashSales->hasPages())
        <div class="p-3 border-top">{{ $flashSales->links() }}</div>
        @endif
    </div>
</div>

{{-- Add Flash Sale Modal --}}
<div class="modal fade" id="addFlashSaleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.flash-sales.store') }}">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-bolt me-2"></i>{{ app()->getLocale()==='bn' ? 'নতুন ফ্ল্যাশ সেল' : 'New Flash Sale' }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Title (English) *</label>
                            <input type="text" class="form-control" name="title_en" required placeholder="e.g. Eid Mega Flash Sale" value="Flash Sale">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label bengali-text">শিরোনাম (বাংলা) *</label>
                            <input type="text" class="form-control bengali-text" name="title_bn" required placeholder="যেমন: ঈদ মেগা ফ্ল্যাশ সেল" value="ফ্ল্যাশ সেল">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ app()->getLocale()==='bn' ? 'শুরুর সময়' : 'Start Time' }} *</label>
                            <input type="datetime-local" class="form-control" name="start_time" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ app()->getLocale()==='bn' ? 'শেষের সময়' : 'End Time' }} *</label>
                            <input type="datetime-local" class="form-control" name="end_time" value="{{ now()->addHours(24)->format('Y-m-d\TH:i') }}" required>
                        </div>
                    </div>
                    <div class="alert alert-info mb-0"><i class="fas fa-info-circle me-2"></i>{{ app()->getLocale()==='bn' ? 'তৈরি করার পর পণ্য যোগ করতে পারবেন এবং সেল চালু করতে পারবেন।' : 'After creating, you can add products and activate the sale.' }}</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-bolt me-2"></i>Create Flash Sale</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
