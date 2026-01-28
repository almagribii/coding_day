# 🎨 Visual Design System - Cheat Sheet

## Color Palette

### Primary Colors
```
Dark Navy:       #0f0f1e   ████ Background utama
Dark Purple:     #1a1a2e   ████ Background secondary
Card Dark:       #16213e   ████ Card background
```

### Accent Colors
```
Bright Cyan:     #00b4ff   ████ Primary Action (Blue)
Teal Green:      #00d4aa   ████ Success / Verified (Green)
Vibrant Purple:  #a855f7   ████ Special Items
Warm Orange:     #ff6b35   ████ Warnings / Alerts
Hot Pink:        #ff006e   ████ Highlights
Vibrant Red:     #ff4757   ████ Errors
Gold:            #ffd60a   ████ Premium / Badges
```

### Text Colors
```
Off White:       #e8eaed   ████ Main Text
Light White:     #f5f5f5   ████ Light Text
Gray:            #9ca3af   ████ Muted Text
Dark Gray:       #2d3748   ████ Borders
```

---

## UI Component Examples

### Stat Box
```
┌─────────────────────┐
│  📊 ICON            │
│                     │
│      245            │  ← Large number
│                     │
│  METRIC LABEL       │
└─────────────────────┘
```
**Colors**: Cyan icon, text in accent color, background gradient

### Card Component
```
┌─────────────────────────────────────┐
│ Header with gradient background     │
├─────────────────────────────────────┤
│ Content area                        │
│ • Better spacing                    │
│ • Readable typography               │
│                                     │
└─────────────────────────────────────┘
```
**Hover Effect**: Lifts up, shadow increases, border glows

### Button Styles
```
PRIMARY:         [ Login ] ← Blue gradient with shadow
SUCCESS:         [ Submit ] ← Green gradient with shadow
DANGER:          [ Delete ] ← Red gradient with shadow
```

### Rank Badges
```
🥇 #1    ← Gold gradient, strong shadow
🥈 #2    ← Silver gradient, strong shadow
🥉 #3    ← Bronze gradient, strong shadow
```

---

## Typography Scale

```
Heading 1:  3rem / 48px   (Bold - 800 weight)
Heading 2:  2.5rem / 40px (Bold - 800 weight)
Heading 3:  2rem / 32px   (Bold - 700 weight)
Heading 5:  1.25rem / 20px (Bold - 700 weight)
Body:       1rem / 16px   (Regular - 400 weight)
Small:      0.875rem / 14px (Regular - 400 weight)
Tiny:       0.75rem / 12px (Regular - 400 weight)
```

---

## Spacing System

```
Minimal:         0.25rem (4px)
Extra Small:     0.5rem  (8px)
Small:           0.75rem (12px)
Base:            1rem    (16px)    ← Default
Medium:          1.5rem  (24px)
Large:           2rem    (32px)
Extra Large:     2.5rem  (40px)
Jumbo:           3rem    (48px)
```

---

## Shadow Effects

### Subtle Shadow
```
box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
```
Usage: Cards on hover, buttons

### Medium Shadow
```
box-shadow: 0 8px 16px rgba(0, 180, 255, 0.2);
```
Usage: Elevated cards, modals

### Strong Shadow
```
box-shadow: 0 12px 24px rgba(0, 180, 255, 0.4);
```
Usage: Rank badges, featured items

### Combined Shadow
```
box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4), 0 0 30px rgba(0, 180, 255, 0.1);
```
Usage: Login card, hero sections

---

## Animation Timings

```
Quick:     0.2s ease    (Subtle hover effects)
Standard:  0.3s ease    (Most interactions)
Medium:    0.4s ease    (Larger animations)
Slow:      0.5s ease    (Page transitions)
```

Easing: `cubic-bezier(0.4, 0, 0.2, 1)` (smooth, professional feel)

---

## Gradient Patterns

### Simple 135deg Gradient
```css
linear-gradient(135deg, #00b4ff 0%, #0077cc 100%)
```

### Glass Effect
```css
background: rgba(255, 255, 255, 0.05);
backdrop-filter: blur(10px);
border: 1px solid rgba(255, 255, 255, 0.1);
```

### Radial Glow
```css
background: radial-gradient(circle, rgba(0, 180, 255, 0.1), transparent);
```

---

## Border Radius

```
Subtle:     6px    (Buttons, inputs)
Standard:   8px    (Cards, modals)
Rounded:    12px   (Larger cards)
More:       16px   (Hero sections)
Max:        50%    (Badges, circles)
```

---

## Hover States

### Card Hover
```
Transform:  translateY(-4px) → translateY(-8px)
Border:     solid → glowing
Shadow:     0 8px 16px → 0 16px 32px
Transition: 0.3s ease
```

### Button Hover
```
Transform:  translateY(-2px) → translateY(-3px)
Shadow:     box-shadow increases
Duration:   0.3s
```

### Table Row Hover
```
Background: transparent → rgba(0, 180, 255, 0.05)
Transform:  scale(1.01)
Duration:   0.3s
```

---

## Responsive Breakpoints

```
Mobile:    ≤ 767px      (Stack vertically)
Tablet:    768px-1023px (2-column layout)
Desktop:   1024px+      (Full layout)
Large:     1440px+      (Max width containers)
```

---

## Accessibility

### Color Contrast
```
✅ AAA Standard (7:1 ratio)
✅ White on Blue (#e8eaed on #00b4ff)
✅ White on Dark (#e8eaed on #0f0f1e)
```

### Touch Targets
```
✅ Minimum 44x44px for buttons
✅ Better spacing on mobile
✅ Large tap areas
```

### Icons
```
✅ Always paired with text
✅ Clear, recognizable
✅ Proper size ratios
```

---

## Component States

### Default State
```
Border:     1px solid border-color
Background: card-bg
Text:       text-white
```

### Hover State
```
Border:     2px solid accent-blue
Background: lighter
Shadow:     box-shadow increases
Transform:  translateY(-4px)
```

### Active State
```
Border:     2px solid accent-blue
Background: darker
Shadow:     minimal
Transform:  translateY(0)
```

### Disabled State
```
Opacity:    0.5
Cursor:     not-allowed
Color:      text-muted
```

---

## Form Elements

### Input Focus
```
Border:     2px solid --accent-blue
Shadow:     0 0 0 3px rgba(0, 180, 255, 0.1)
Background: lighter gradient
Duration:   0.3s
```

### Input Placeholder
```
Color:      --text-muted
Opacity:    0.6
```

### Label
```
Color:      --text-white
Weight:     600
Size:       0.95rem
Margin:     0.75rem bottom
```

---

## Icon System

### Icon Sizing
```
Navbar:         1.25rem
Buttons:        1rem
Stats:          2.5rem
Headers:        3rem
Hero:           4rem
```

### Icon Colors
```
Primary:        var(--accent-blue)
Success:        var(--accent-green)
Warning:        var(--accent-orange)
Error:          var(--accent-red)
Special:        var(--accent-purple)
Premium:        var(--accent-yellow)
```

---

## Dark Mode Notes

- ✅ Dark backgrounds (#0f0f1e, #1a1a2e)
- ✅ Light text (#e8eaed, #f5f5f5)
- ✅ Bright accent colors for contrast
- ✅ No eye strain at night
- ✅ Professional appearance

---

## Best Practices

1. **Use CSS Variables** - Change colors globally
2. **Consistent Spacing** - Follow the scale
3. **Meaningful Colors** - Green = good, Red = bad
4. **Smooth Transitions** - 0.3s-0.4s standard
5. **Proper Shadows** - Add depth without clutter
6. **Readable Typography** - Good hierarchy
7. **Mobile First** - Design for small screens
8. **Test Contrast** - Accessibility matters

---

## Quick Reference Commands

### Check Color Contrast
```
Use: webaim.org/resources/contrastchecker/
Target: AAA (7:1 ratio)
```

### Generate Gradients
```
Use: cssgradient.io
Pick colors from palette
Copy-paste CSS
```

### Find Hex Colors
```
Use: color-hex.com
Search by name
Get exact codes
```

---

## Color Psychology

- **Blue** (#00b4ff) - Trust, professionalism, action
- **Green** (#00d4aa) - Success, verification, growth
- **Purple** (#a855f7) - Creativity, special
- **Orange** (#ff6b35) - Attention, warning, energy
- **Red** (#ff4757) - Errors, danger, important
- **Yellow** (#ffd60a) - Premium, achievement, highlight

---

**This is your design system! Keep it consistent across all updates.** ✨
