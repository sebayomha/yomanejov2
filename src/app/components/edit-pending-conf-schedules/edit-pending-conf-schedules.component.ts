import { Component, Input, Output, EventEmitter } from '@angular/core';
import { trigger, transition, animate, style } from '@angular/animations';

@Component({
  selector: 'edit-pending-conf-schedules',
  templateUrl: './edit-pending-conf-schedules.component.html',
  styleUrls: ['./edit-pending-conf-schedules.component.scss'],
})

export class EditPendingConfSchedulesComponent {

  @Input() cronograma;
  @Output() show_edit = new EventEmitter<string>();

  constructor() { }

  ngOnInit() { }

  //Cierro edicion
  closeEditCrono(flag) {
    this.show_edit.emit(flag);
  }

}