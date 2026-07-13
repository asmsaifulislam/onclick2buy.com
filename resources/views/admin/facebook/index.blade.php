@extends('layouts.admin')

@section('title', 'Facebook Integration')

@section('content')
<div style="max-width:900px; margin:0 auto; padding:24px;">

    <div style="display:flex; align-items:center; gap:16px; margin-bottom:32px;">
        <div style="width:48px; height:48px; border-radius:12px; background:#1877F2; display:flex; align-items:center; justify-content:center;">
            <svg style="width:28px; height:28px; color:#fff;" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
        </div>
        <div>
            <h1 style="font-size:24px; font-weight:700; color:#1f2937; margin:0;">Facebook Integration</h1>
            <p style="font-size:14px; color:#6b7280; margin:4px 0 0;">Publish products to your Facebook Page</p>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#ecfdf5; border:1px solid #a7f3d0; border-radius:8px; padding:12px 16px; margin-bottom:24px; color:#065f46; font-size:14px;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:8px; padding:12px 16px; margin-bottom:24px; color:#991b1b; font-size:14px;">
            {{ session('error') }}
        </div>
    @endif

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:32px;">
        <div style="background:{{ $isConfigured ? '#ecfdf5' : '#fef2f2' }}; border:1px solid {{ $isConfigured ? '#a7f3d0' : '#fecaca' }}; border-radius:12px; padding:20px; display:flex; align-items:center; gap:16px;">
            <div style="width:40px; height:40px; border-radius:50%; background:{{ $isConfigured ? '#10b981' : '#ef4444' }}; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                @if($isConfigured)
                    <svg style="width:20px; height:20px; color:#fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                @else
                    <svg style="width:20px; height:20px; color:#fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                @endif
            </div>
            <div>
                <p style="font-weight:600; color:#1f2937; margin:0; font-size:15px;">{{ $isConfigured ? 'Connected' : 'Not Configured' }}</p>
                <p style="font-size:13px; color:#6b7280; margin:4px 0 0;">{{ $isConfigured ? 'Your Facebook Page is connected' : 'Set up your Facebook Page below' }}</p>
            </div>
        </div>

        <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:12px; padding:20px; display:flex; align-items:center; gap:16px;">
            <div style="width:40px; height:40px; border-radius:50%; background:#3b82f6; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg style="width:20px; height:20px; color:#fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
                <p style="font-weight:600; color:#1f2937; margin:0; font-size:15px;">Quick Publish</p>
                <p style="font-size:13px; color:#6b7280; margin:4px 0 0;">Post products directly from the Products page</p>
            </div>
        </div>
    </div>

    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:28px; margin-bottom:24px; box-shadow:0 1px 3px rgba(0,0,0,0.08);">
        <h2 style="font-size:18px; font-weight:600; color:#1f2937; margin:0 0 4px;">Page Configuration</h2>
        <p style="font-size:13px; color:#6b7280; margin:0 0 20px;">Enter your Facebook Page credentials to enable posting.</p>

        <form method="POST" action="{{ route('admin.facebook.update') }}">
            @csrf
            @method('PUT')

            <div style="margin-bottom:20px;">
                <label style="display:block; font-size:14px; font-weight:500; color:#374151; margin-bottom:6px;">Facebook Page ID</label>
                <input type="text" name="fb_page_id" value="{{ $settings['fb_page_id'] ?? '' }}" placeholder="e.g. 123456789012345"
                    style="width:100%; padding:10px 14px; border:1px solid #d1d5db; border-radius:8px; font-size:14px; box-sizing:border-box; {{ $errors->has('fb_page_id') ? 'border-color:#ef4444;' : '' }}">
                @if($errors->has('fb_page_id'))
                    <p style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $errors->first('fb_page_id') }}</p>
                @endif
                <p style="font-size:12px; color:#9ca3af; margin-top:4px;">Find this in your Facebook Page Settings &gt; About &gt; Page ID</p>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block; font-size:14px; font-weight:500; color:#374151; margin-bottom:6px;">Page Access Token</label>
                <input type="password" name="fb_page_access_token" value="{{ $settings['fb_page_access_token'] ?? '' }}" placeholder="Enter your Page Access Token"
                    style="width:100%; padding:10px 14px; border:1px solid #d1d5db; border-radius:8px; font-size:14px; box-sizing:border-box; {{ $errors->has('fb_page_access_token') ? 'border-color:#ef4444;' : '' }}">
                @if($errors->has('fb_page_access_token'))
                    <p style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $errors->first('fb_page_access_token') }}</p>
                @endif
                <p style="font-size:12px; color:#9ca3af; margin-top:4px;">Generate from Graph API Explorer with <code style="background:#f3f4f6; padding:1px 4px; border-radius:3px;">pages_manage_posts</code> and <code style="background:#f3f4f6; padding:1px 4px; border-radius:3px;">pages_read_engagement</code> permissions</p>
            </div>

            <div style="display:flex; gap:12px; align-items:center;">
                <button type="submit" style="background:#1877F2; color:#fff; padding:10px 24px; border:none; border-radius:8px; font-size:14px; font-weight:500; cursor:pointer;">
                    Save Settings
                </button>
                <button type="button" onclick="testFacebookConnection()" id="test-btn" style="background:#f3f4f6; color:#374151; padding:10px 20px; border:1px solid #d1d5db; border-radius:8px; font-size:14px; cursor:pointer;">
                    Test Connection
                </button>
                <span id="test-result" style="font-size:13px;"></span>
            </div>
        </form>
    </div>

    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:28px; margin-bottom:24px; box-shadow:0 1px 3px rgba(0,0,0,0.08);">
        <h2 style="font-size:18px; font-weight:600; color:#1f2937; margin:0 0 4px;">Setup Guide</h2>
        <p style="font-size:13px; color:#6b7280; margin:0 0 20px;">Follow these steps to connect your Facebook Page.</p>

        <div style="display:flex; flex-direction:column; gap:16px;">
            <div style="display:flex; gap:16px; align-items:flex-start;">
                <div style="width:32px; height:32px; border-radius:50%; background:#1877F2; color:#fff; display:flex; align-items:center; justify-content:center; font-size:14px; font-weight:600; flex-shrink:0;">1</div>
                <div>
                    <p style="font-weight:500; color:#1f2937; margin:0 0 4px; font-size:14px;">Create a Facebook App</p>
                    <p style="font-size:13px; color:#6b7280; margin:0;">Go to <a href="https://developers.facebook.com/apps/" target="_blank" style="color:#1877F2;">developers.facebook.com/apps</a> and create a new app. Select "Business" type.</p>
                </div>
            </div>

            <div style="display:flex; gap:16px; align-items:flex-start;">
                <div style="width:32px; height:32px; border-radius:50%; background:#1877F2; color:#fff; display:flex; align-items:center; justify-content:center; font-size:14px; font-weight:600; flex-shrink:0;">2</div>
                <div>
                    <p style="font-weight:500; color:#1f2937; margin:0 0 4px; font-size:14px;">Add a Facebook Page</p>
                    <p style="font-size:13px; color:#6b7280; margin:0;">In your app dashboard, go to Settings &gt; Basic and add your Facebook Page under "App Page".</p>
                </div>
            </div>

            <div style="display:flex; gap:16px; align-items:flex-start;">
                <div style="width:32px; height:32px; border-radius:50%; background:#1877F2; color:#fff; display:flex; align-items:center; justify-content:center; font-size:14px; font-weight:600; flex-shrink:0;">3</div>
                <div>
                    <p style="font-weight:500; color:#1f2937; margin:0 0 4px; font-size:14px;">Generate a Page Access Token</p>
                    <p style="font-size:13px; color:#6b7280; margin:0;">Go to <a href="https://developers.facebook.com/tools/explorer/" target="_blank" style="color:#1877F2;">Graph API Explorer</a>, select your app, click "Generate Access Token", and grant <code style="background:#f3f4f6; padding:1px 4px; border-radius:3px;">pages_manage_posts</code> and <code style="background:#f3f4f6; padding:1px 4px; border-radius:3px;">pages_read_engagement</code> permissions.</p>
                </div>
            </div>

            <div style="display:flex; gap:16px; align-items:flex-start;">
                <div style="width:32px; height:32px; border-radius:50%; background:#1877F2; color:#fff; display:flex; align-items:center; justify-content:center; font-size:14px; font-weight:600; flex-shrink:0;">4</div>
                <div>
                    <p style="font-weight:500; color:#1f2937; margin:0 0 4px; font-size:14px;">Get your Page ID</p>
                    <p style="font-size:13px; color:#6b7280; margin:0;">Go to your Facebook Page &gt; About &gt; Page ID. Copy and paste it above.</p>
                </div>
            </div>

            <div style="display:flex; gap:16px; align-items:flex-start;">
                <div style="width:32px; height:32px; border-radius:50%; background:#1877F2; color:#fff; display:flex; align-items:center; justify-content:center; font-size:14px; font-weight:600; flex-shrink:0;">5</div>
                <div>
                    <p style="font-weight:500; color:#1f2937; margin:0 0 4px; font-size:14px;">Save & Test</p>
                    <p style="font-size:13px; color:#6b7280; margin:0;">Click "Save Settings" then "Test Connection" to verify everything works. Once connected, use the "Post" button on any product to publish it to Facebook.</p>
                </div>
            </div>
        </div>
    </div>

    <div id="recent-posts-section" style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:28px; box-shadow:0 1px 3px rgba(0,0,0,0.08);">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
            <div>
                <h2 style="font-size:18px; font-weight:600; color:#1f2937; margin:0 0 4px;">Recent Posts</h2>
                <p style="font-size:13px; color:#6b7280; margin:0;">Your latest Facebook Page posts</p>
            </div>
            <button onclick="loadRecentPosts()" style="background:#f3f4f6; color:#374151; padding:8px 16px; border:1px solid #d1d5db; border-radius:8px; font-size:13px; cursor:pointer;">Refresh</button>
        </div>
        <div id="recent-posts-list">
            <p style="font-size:13px; color:#9ca3af; text-align:center; padding:20px 0;">Click "Refresh" to load recent posts</p>
        </div>
    </div>
</div>

<script>
function testFacebookConnection() {
    const btn = document.getElementById('test-btn');
    const result = document.getElementById('test-result');
    btn.disabled = true;
    btn.textContent = 'Testing...';
    result.innerHTML = '';

    fetch('{{ route("admin.facebook.test") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            result.innerHTML = '<span style="color:#059669;">&#10003; ' + data.message + '</span>';
        } else {
            result.innerHTML = '<span style="color:#dc2626;">&#10007; ' + data.error + '</span>';
        }
    })
    .catch(() => {
        result.innerHTML = '<span style="color:#dc2626;">&#10007; Request failed</span>';
    })
    .finally(() => {
        btn.disabled = false;
        btn.textContent = 'Test Connection';
    });
}

function loadRecentPosts() {
    const container = document.getElementById('recent-posts-list');
    container.innerHTML = '<p style="font-size:13px; color:#9ca3af; text-align:center; padding:20px 0;">Loading...</p>';

    fetch('{{ route("admin.facebook.recent-posts") }}', {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.data && data.data.length > 0) {
            container.innerHTML = data.data.map(post => {
                const date = new Date(post.created_time).toLocaleDateString();
                const msg = post.message ? (post.message.length > 120 ? post.message.substring(0, 120) + '...' : post.message) : 'No message';
                const img = post.full_picture ? '<img src="' + post.full_picture + '" style="width:48px; height:48px; border-radius:8px; object-fit:cover; flex-shrink:0;">' : '';
                const link = post.permalink_url ? '<a href="' + post.permalink_url + '" target="_blank" style="color:#1877F2; font-size:12px; text-decoration:none;">View on Facebook &#8599;</a>' : '';
                return '<div style="display:flex; gap:12px; padding:12px; border:1px solid #f3f4f6; border-radius:8px; margin-bottom:8px; align-items:center;">' + img + '<div style="flex:1; min-width:0;"><p style="font-size:13px; color:#374151; margin:0 0 4px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">' + msg + '</p><div style="display:flex; gap:12px; align-items:center;">' + link + '<span style="font-size:12px; color:#9ca3af;">' + date + '</span></div></div></div>';
            }).join('');
        } else {
            container.innerHTML = '<p style="font-size:13px; color:#9ca3af; text-align:center; padding:20px 0;">No posts found. Make sure your token has the right permissions.</p>';
        }
    })
    .catch(() => {
        container.innerHTML = '<p style="font-size:13px; color:#dc2626; text-align:center; padding:20px 0;">Failed to load posts</p>';
    });
}
</script>
@endsection
