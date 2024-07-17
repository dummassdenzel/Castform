import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { Observable, map, shareReplay, timer } from 'rxjs';

@Component({
  selector: 'app-real-time-clock',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './real-time-clock.component.html',
  styleUrl: './real-time-clock.component.css'
})
export class RealTimeClockComponent {
  
  public time$: Observable<Date>;
  public dateToday$: Observable<string>;

  constructor(){
    this.time$ = timer(0, 1000).pipe(
      map(() => new Date()),
      shareReplay(1)
    );

    this.dateToday$ = timer(0, 1000 * 60 * 60 * 24).pipe(
      map(() => {
        const today = new Date();
        return today.toDateString();
      }),
      shareReplay(1)
    );
  }
}
