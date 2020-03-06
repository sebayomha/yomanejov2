import { Component, Input } from '@angular/core';
import { Address } from '../../models/address.model';
import { DatesTimes } from '../../models/dates-times';
import { Option } from '../../models/option';


@Component({
  selector: 'edit-pending-conf-schedules',
  templateUrl: './edit-pending-conf-schedules.component.html',
  styleUrls: ['./edit-pending-conf-schedules.component.scss']
})

export class EditPendingConfSchedulesComponent {


  @Input() cronograma;

  constructor() { }

  ngOnInit() { }

}