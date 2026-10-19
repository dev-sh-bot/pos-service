<button
    type="button"
    class="snd-theme-toggle"
    data-theme-toggle
    data-label-light="{{ __('common.Switch_to_light_theme') }}"
    data-label-dark="{{ __('common.Switch_to_dark_theme') }}"
    aria-label="{{ __('common.Switch_to_dark_theme') }}"
    title="{{ __('common.Switch_to_dark_theme') }}"
    aria-pressed="false"
>
    <i class="fas fa-moon snd-theme-icon snd-theme-icon-dark" aria-hidden="true"></i>
    <i class="fas fa-sun snd-theme-icon snd-theme-icon-light" aria-hidden="true"></i>
    <span class="snd-theme-toggle-label">{{ __('common.Theme') }}</span>
</button>
