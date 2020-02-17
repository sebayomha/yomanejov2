import { Component, Input } from '@angular/core';
declare var $:any;

@Component({
  selector: 'schedule-tooltip',
  templateUrl: './schedule-tooltip.component.html',
  styleUrls: ['./schedule-tooltip.component.scss']
})

export class ScheduleTooltipComponent {

    @Input() id_car:number;
    @Input() info_schedule_car: any;
    tooltipString: string = '';
    constructor() { }

    ngOnInit() {
        this.tooltipString = '<br>';
        this.info_schedule_car.forEach(element => {
            this.tooltipString = this.tooltipString.concat('<tr><td>Horario: ' +element.horario+'hs&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Alumno: </td><td>'+ element.alumno +'</td></tr><br><tr><td>Dirección: '+ element.direccion+ '</td></tr><br><br>');
        });
     }
}