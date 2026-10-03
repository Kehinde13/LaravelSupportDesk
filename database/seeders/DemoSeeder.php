<?php

namespace Database\Seeders;

use App\Models\User;
use App\TicketPriority;
use App\TicketStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use LogicException;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new LogicException('DemoSeeder is restricted to local development and testing.');
        }

        // Public demonstration credentials: never use this account in production.
        DB::transaction(function (): void {
            $user = User::firstOrNew(['email' => 'demo@supportdesk.test']);
            $user->name = 'Demo User';
            $user->password = 'password';
            $user->email_verified_at ??= Carbon::now();
            $user->save();

            $tickets = [
                ['VPN connection fails after password reset', 'The VPN client rejects my new password after yesterday\'s account reset. Web applications accept the password. Please check the remote-access authentication settings.', TicketPriority::High, TicketStatus::Open, 1],
                ['Shared printer is unavailable on the second floor', 'The second-floor printer appears offline for the finance team. It is powered on and connected to the network, but queued documents do not print.', TicketPriority::Medium, TicketStatus::InProgress, 2],
                ['Request access to the project reporting folder', 'Please grant read access to the shared reporting folder for the quarterly review. My manager has approved access for the duration of the project.', TicketPriority::Low, TicketStatus::Open, 3],
                ['Email delivery delayed for external recipients', 'Messages to external partners remain in the outbox for several minutes. Internal messages arrive normally. Please investigate the outbound mail connection.', TicketPriority::High, TicketStatus::InProgress, 5],
                ['Meeting room display loses its connection', 'The display in meeting room B disconnects during presentations. Reconnecting the HDMI cable restores the picture temporarily. Please inspect the cable and adapter.', TicketPriority::Medium, TicketStatus::Open, 7],
                ['Install the approved PDF editing software', 'The approved PDF editor has been installed and activated on my workstation. I have confirmed that I can annotate and export documents successfully.', TicketPriority::Low, TicketStatus::Resolved, 9],
                ['Laptop battery drains during standby', 'The laptop lost most of its charge overnight while in standby. A firmware update and power-setting adjustment have resolved the issue during follow-up testing.', TicketPriority::Medium, TicketStatus::Resolved, 12],
                ['Restore access to the customer support mailbox', 'Access to the shared support mailbox has been restored after the group membership was corrected. Incoming messages and shared replies are working again.', TicketPriority::High, TicketStatus::Resolved, 14],
            ];

            foreach ($tickets as [$title, $description, $priority, $status, $daysAgo]) {
                // Stable titles identify these local demo records on repeat runs.
                $ticket = $user->tickets()->firstOrNew(['title' => $title]);
                $ticket->fill(compact('description', 'priority', 'status'));
                if (! $ticket->exists) {
                    $ticket->created_at = now()->subDays($daysAgo)->setTime(9, 0);
                    $ticket->updated_at = $ticket->created_at;
                }
                $ticket->save();
            }
        });
    }
}
