<x-app-layout
    title="Reminder settings"
    description="Choose when tenants get automatic rent reminder emails."
>
    <x-ui.flash />

    <x-ui.card>
        <form
            method="POST"
            action="{{ route('settings.update', $setting) }}"
            class="max-w-2xl space-y-8"
            x-data="{ emailsEnabled: @js((bool) old('email_reminders_enabled', $setting->email_reminders_enabled)) }"
        >
            @csrf
            @method('PUT')

            <div class="ui-toggle-row">
                <div>
                    <p class="text-sm font-semibold text-white">Email reminders</p>
                    <p class="mt-1 text-sm text-slate-400">
                        Send automatic emails to tenants before and after rent is due.
                    </p>
                </div>
                <label class="relative inline-flex cursor-pointer items-center">
                    <input type="hidden" name="email_reminders_enabled" value="0">
                    <input
                        type="checkbox"
                        name="email_reminders_enabled"
                        value="1"
                        class="peer sr-only"
                        x-model="emailsEnabled"
                        @checked(old('email_reminders_enabled', $setting->email_reminders_enabled))
                    >
                    <span
                        class="h-7 w-12 rounded-full bg-slate-300 transition peer-checked:bg-brand-gradient
                            after:absolute after:left-0.5 after:top-0.5 after:h-6 after:w-6 after:rounded-full
                            after:bg-white after:shadow-sm after:transition
                            peer-checked:after:translate-x-5"
                        aria-hidden="true"
                    ></span>
                </label>
            </div>

            <div
                class="space-y-8 transition-opacity duration-200"
                :class="emailsEnabled ? 'opacity-100' : 'pointer-events-none opacity-40'"
            >
                <x-settings.day-picker
                    name="reminder_days_before"
                    label="Before rent is due"
                    hint="Tap the days you want a reminder sent ahead of the due date."
                    :options="[
                        14 => '14 days',
                        7 => '7 days',
                        3 => '3 days',
                        1 => '1 day',
                    ]"
                    :selected="$setting->reminder_days_before"
                />

                <div class="border-t border-white/[0.06]"></div>

                <x-settings.day-picker
                    name="overdue_reminder_days"
                    label="If rent is late"
                    hint="Tap the days after the due date to send a follow-up reminder."
                    :options="[
                        1 => '1 day',
                        3 => '3 days',
                        7 => '7 days',
                        14 => '14 days',
                    ]"
                    :selected="$setting->overdue_reminder_days"
                />
            </div>

            <p
                x-show="!emailsEnabled"
                x-cloak
                class="text-sm text-slate-400"
            >
                Reminders are paused. Turn email reminders on to edit the schedule.
            </p>

            <div class="flex items-center gap-3 border-t border-slate-200/80 pt-6">
                <x-ui.button type="submit">Save settings</x-ui.button>
            </div>
        </form>
    </x-ui.card>
</x-app-layout>
