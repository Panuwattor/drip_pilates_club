@foreach($sessions as $s)
  @php
    $trainer = $s->actualTrainer();
    $booking = $myBookings[$s->id] ?? null;
    $spots = $s->spotsLeft();
    $full = $s->isFull();
  @endphp

  <div class="col" data-session="{{ $s->id }}">
    <div class="class-card mb-0 h-100">
      <div class="class-time-rail">
        {{ $s->start_at->format('H:i') }}
        <small>{{ (int) $s->start_at->diffInMinutes($s->end_at) }} {{ app()->getLocale() === 'th' ? 'นาที' : 'min' }}</small>
      </div>

      <div class="class-info">
        <div class="d-flex justify-content-between align-items-start gap-2">
          <div class="min-width-0">
            <h4>{{ $s->classType->name }}</h4>
            <p class="meta">
              @if($trainer?->avatar)
                <img src="{{ asset($trainer->avatar) }}" alt="" class="coach-avatar">
              @endif
              <span>{{ $trainer?->nickname ?: $trainer?->name ?: '—' }}</span>
              @if($s->substitute_trainer_id)
                <span class="badge rounded-pill" style="background:#F4E3C7;color:#8A6112;font-size:.62rem;">
                  {{ app()->getLocale() === 'th' ? 'สอนแทน' : 'Sub' }}
                </span>
              @endif
              @if($s->room) · <span>{{ $s->room->name }}</span> @endif
            </p>
          </div>

          @if($booking && $booking->status === 'confirmed')
            <span class="badge rounded-pill" style="background:#D8ECD9;color:#2F6B33;">
              {{ app()->getLocale() === 'th' ? 'จองแล้ว' : 'Booked' }}
            </span>
          @elseif($booking && $booking->status === 'waitlisted')
            <span class="badge rounded-pill" style="background:#F4E3C7;color:#8A6112;">
              {{ app()->getLocale() === 'th' ? 'คิวที่ ' . $booking->waitlist_position : 'Queue #' . $booking->waitlist_position }}
            </span>
          @elseif($full)
            <span class="badge rounded-pill" style="background:var(--accent-soft);color:var(--accent-deep);">
              {{ app()->getLocale() === 'th' ? 'เต็ม' : 'Full' }}
            </span>
          @elseif($spots <= 2)
            <span class="badge rounded-pill" style="background:#F4E3C7;color:#8A6112;">
              {{ app()->getLocale() === 'th' ? "เหลือ {$spots} ที่" : "{$spots} left" }}
            </span>
          @else
            <span class="badge rounded-pill" style="background:var(--sage-soft);color:var(--sage);">
              {{ app()->getLocale() === 'th' ? "เหลือ {$spots} ที่" : "{$spots} spots left" }}
            </span>
          @endif
        </div>

        @if($booking && in_array($booking->status, ['confirmed', 'waitlisted']))
          <button class="btn btn-waitlist btn-sm border mt-2 js-cancel" type="button"
                  data-booking="{{ $booking->id }}">
            {{ app()->getLocale() === 'th' ? 'ยกเลิกการจอง' : 'Cancel booking' }}
          </button>
        @elseif(! $s->isBookable())
          <button class="btn btn-waitlist btn-sm border mt-2" type="button" disabled>
            {{ app()->getLocale() === 'th' ? 'ปิดรับจองแล้ว' : 'Booking closed' }}
          </button>
        @elseif($full)
          <button class="btn btn-waitlist btn-sm border mt-2 js-book" type="button" data-session="{{ $s->id }}">
            {{ app()->getLocale() === 'th' ? 'เข้าคิว Waitlist' : 'Join Waitlist' }}
          </button>
        @else
          <button class="btn btn-book btn-sm mt-2 js-book" type="button" data-session="{{ $s->id }}">
            {{ app()->getLocale() === 'th' ? 'จองเลย' : 'Book Now' }}
          </button>
        @endif
      </div>
    </div>
  </div>
@endforeach
