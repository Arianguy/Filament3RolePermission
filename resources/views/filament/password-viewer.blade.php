<div class="card p-4 shadow-sm">
    <h4 class="card-title mb-4">Passwords</h4>
    <div class="table-responsive">
        <table class="table table-borderless">
            <tbody>
                <tr>
                    <td><strong>Main Password:</strong></td>
                    <td>{{ $record->main_password }}</td>
                </tr>
                <tr>
                    <td><strong>PC Outlook Password:</strong></td>
                    <td>{{ $record->pc_outlook_password }}</td>
                </tr>
                <tr>
                    <td><strong>iOS Outlook Password:</strong></td>
                    <td>{{ $record->ios_outlook_password }}</td>
                </tr>
                <tr>
                    <td><strong>Android Outlook Password:</strong></td>
                    <td>{{ $record->android_outlook_password }}</td>
                </tr>
                <tr>
                    <td><strong>Other Password:</strong></td>
                    <td>{{ $record->other_password }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
