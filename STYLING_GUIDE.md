# Custom Filament Styling Guide

## Overview

Komprehensif custom styling telah diterapkan ke seluruh panel Filament KOMITE untuk memberikan pengalaman pengguna yang polished dan modern dengan animasi, gradien, dan efek visual yang konsisten.

## File Struktur CSS

Semua custom styling berada di `resources/css/filament/kelola/` dan di-import melalui `theme.css`:

### 1. **dashboard.css**
Styling untuk halaman dashboard dengan stat cards, charts, dan widgets.

**Fitur:**
- Gradient background dengan dot pattern overlay
- Animated stat cards dengan glow effects dan left border
- Section headers dengan accent bar
- Smooth hover animations dengan scale dan shadow
- Custom scrollbar styling
- Staggered animations untuk cascading entrance

**Komponen:**
- `.fi-page-dashboard` - container halaman
- `.fi-wi-stats-overview-stat` - stat card styling
- `.fi-section-header` - section title styling
- `.quick-actions-grid` - 2-column grid layout untuk quick actions

### 2. **sidebar.css**
Styling untuk sidebar navigation dengan polished menu items.

**Fitur:**
- Gradient background dengan subtle pattern
- Animated menu items dengan staggered delays
- Active state dengan left border amber dan gradient
- Icon animations (scale dan color pada hover)
- Badge styling dengan pulsing animation
- Smooth expand/collapse animation untuk submenus
- Custom scrollbar dengan amber accent

**Komponen:**
- `.fi-sidebar` - sidebar container
- `.fi-sidebar-nav-item` - menu item styling
- `.fi-sidebar-nav-item.fi-active` - active menu state
- `.fi-sidebar-nav-item-badge` - notification badge

### 3. **topbar.css**
Styling untuk top navigation bar dengan dropdown menus.

**Fitur:**
- Gradient background dengan backdrop blur
- Search bar dengan focus states
- Action button styling dengan hover effects
- User menu trigger dengan avatar ring
- Dropdown menu dengan staggered item animations
- Notification badges dengan pulsing effect
- Responsive hamburger menu untuk mobile

**Komponen:**
- `.fi-topbar` - header container
- `.fi-topbar-action-btn` - action button styling
- `.fi-dropdown-menu` - dropdown styling
- `.fi-dropdown-menu-item` - dropdown item styling
- `.fi-badge` - notification badge

### 4. **modals-forms.css**
Styling untuk modal, form elements, dan buttons.

**Fitur Modal:**
- Animated backdrop dengan blur effect
- Smooth scale-in animation
- Gradient header dengan subtle border accent
- Custom close button dengan rotate animation
- Scrollable content area

**Fitur Form:**
- Gradient input backgrounds
- Enhanced focus states dengan shadow dan glow
- Error state styling dengan red border
- Input icons dengan color transitions
- Help text dan error message styling

**Fitur Buttons:**
- Primary button dengan gradient background
- Secondary button dengan border style
- Destructive button dengan red gradient
- Ghost button variant
- Hover dan active states dengan translateY animation

**Komponen:**
- `.fi-modal` - modal container
- `.fi-input-wrp` - input wrapper
- `.fi-btn-primary`, `.fi-btn-secondary` - button styling
- `.fi-checkbox`, `.fi-radio` - form controls

### 5. **tables-cards.css**
Styling untuk tables dan cards dengan animations.

**Fitur Table:**
- Gradient header dengan hover effects
- Sortable columns dengan icons
- Staggered row entrance animations
- Hover effects dengan scale dan shadow
- Sticky header support
- Border separators dengan smooth transitions

**Fitur Card:**
- Gradient background matching theme
- Hover lift effect dengan enhanced shadow
- Animated appearance dengan staggered delays
- Separate header, content, footer sections
- Subtle gradient pada footer

**Badge Variants:**
- Primary (amber), Success (green), Danger (red)
- Warning (orange), Info (blue), Gray (neutral)
- Status badges dengan pulsing dot indicator
- Hover scale animation

**Komponen:**
- `.fi-table` - table styling
- `.fi-card` - card container
- `.fi-badge` - badge styling
- `.fi-status-badge` - status indicator

### 6. **notifications.css**
Styling untuk notifications, alerts, dan loading states.

**Fitur Toast:**
- Slide-in animation dari kanan
- Gradient backgrounds dengan colored left border
- Progress bar untuk auto-dismiss countdown
- Custom close button dengan hover effects
- Color variants: success, danger, warning, info

**Fitur Alert:**
- Gradient backgrounds dengan subtle opacity
- Left border matching severity level
- Smooth entrance animation
- Closeable alerts dengan transitions

**Fitur Loading:**
- Spinner animation dengan rotating border
- Size variants: sm, normal, lg
- Loading overlay dengan blur backdrop
- Centered spinner dengan smooth rotation

**Komponen:**
- `.fi-toast` - toast notification
- `.fi-alert` - alert box
- `.fi-spinner` - loading spinner
- `.fi-tooltip` - tooltip styling

## Color Palette

Konsistensi warna di seluruh custom styling:

```css
--color-main-primary: rgb(242, 241, 235)    /* Light background */
--color-main-secondary: rgb(204, 197, 181)  /* Muted text */
--color-main-black: rgb(40, 43, 47)         /* Dark text/bg */
--color-main-white: rgb(255, 255, 255)      /* Pure white */
--color-main-accent: rgb(241, 202, 59)      /* Light amber */
--color-main-amber: rgb(255, 152, 0)        /* Main accent color */
```

## Animation Library

Animasi yang digunakan di seluruh styling:

### Entrance Animations:
- `fi-dashboard-card-in` - fade + translateY
- `fi-sidebar-item-in` - slide dari kiri
- `fi-form-group-in` - fade + translateY
- `fi-table-row-in` - slide dari kiri
- `fi-card-in` - scale + translateY (cubic-bezier)
- `fi-alert-in` - fade + translateY
- `fi-toast-slide-in` - slide dari kanan
- `fi-dropdown-item-in` - slide dari kiri

### Interactive Animations:
- `fi-sidebar-expand` - expand submenu
- `fi-sidebar-collapse` - collapse submenu
- `fi-sidebar-badge-pulse` - pulsing badge
- `fi-badge-pulse` - pulsing effect
- `fi-pulse-green`, `fi-pulse-orange`, `fi-pulse-red` - status pulses
- `fi-spinner-spin` - continuous rotation
- `fi-validation-shake` - input error shake
- `fi-toast-progress` - progress bar countdown

## Responsive Design

Semua styling memiliki responsive breakpoints:

```css
@media (max-width: 768px) {
    /* Tablet adjustments */
}

@media (max-width: 640px) {
    /* Mobile adjustments */
}
```

**Mobile Adjustments:**
- Sidebar menjadi fixed overlay dengan toggle
- Topbar search disembunyikan
- Modal max-width 100%
- Button group menjadi full width stacked
- Font sizes di-scale down untuk readability

## Best Practices

### Styling Hierarchy:
1. Hover states lebih ringan daripada active states
2. Disabled states mengurangi opacity (0.6)
3. Z-index management untuk layering
4. Proper positioning untuk pseudo-elements

### Performance:
- Gunakan CSS variables untuk konsistensi
- Preferensikan transform dan opacity untuk animations (GPU accelerated)
- Avoid expensive properties seperti box-shadow di animated elements
- Use `will-change` sparingly

### Maintenance:
- CSS dipisah per-fitur untuk mudah di-maintain
- Consistent naming convention: `fi-[component]-[state]`
- Staggered animations untuk visual flow
- Dokumentasi inline untuk complex rules

## Customization

Untuk custom styling tambahan:

1. **Buat file CSS baru** di `resources/css/filament/kelola/`
2. **Import di theme.css** sebelum file lain yang depends
3. **Gunakan color variables** dari `@theme` block
4. **Follow naming convention** untuk consistency
5. **Test responsive** di berbagai breakpoints

## Browser Support

Custom styling menggunakan:
- CSS Grid dan Flexbox
- CSS Variables (--color-*)
- Backdrop-filter (blur)
- CSS Animations dan Transitions
- Gradient backgrounds

**Kompatibilitas:**
- Modern browsers (Chrome, Firefox, Safari, Edge)
- CSS variables fallback di theme.css
- Graceful degradation untuk unsupported features

## Troubleshooting

### Styling tidak diterapkan:
- Pastikan file di-import di theme.css
- Clear browser cache (hard refresh: Ctrl+Shift+R)
- Check CSS syntax di browser DevTools
- Verify cascade-layer priority

### Animations tidak smooth:
- Check performance di DevTools
- Reduce animation complexity
- Use `transform` dan `opacity` saja
- Avoid animating expensive properties (box-shadow, border)

### Mobile styling issues:
- Test di actual device/emulator
- Check media query breakpoints
- Verify touch targets (min 44x44px)
- Test keyboard navigation

## Future Enhancements

Potential improvements:
- Dark mode support dengan CSS variables
- Custom theme builder untuk color palette
- Animation library dokumentasi lengkap
- Component storybook untuk preview
- Accessibility (a11y) improvements
