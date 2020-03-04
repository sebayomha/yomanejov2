import { Component, Input } from '@angular/core';

@Component({
  selector: 'edit-pending-conf-schedules',
  templateUrl: './edit-pending-conf-schedules.component.html',
  styleUrls: ['./edit-pending-conf-schedules.component.scss']
})

export class EditPendingConfSchedulesComponent {


  @Input() cronograma;

  constructor() { }

  ngOnInit() {
    console.log('crono',this.cronograma);
  }

  getDay(i: number) {
    switch (i) {
      case 0:
        return 'Lunes';
      case 1:
        return 'Martes';
      case 2:
        return 'Miércoles';
      case 3:
        return 'Jueves';
      case 4:
        return 'Viernes';
      case 5:
        return 'Sábado';
      case 6:
        return 'Domingo';
    }
  }

}