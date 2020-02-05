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
        this.tooltipString = '<br><ul>';
        this.info_schedule_car.forEach(element => {
            this.tooltipString = this.tooltipString.concat('<li>Alumno: ' + element.alumno +'<br>Dire: '+ element.direccion +'<br> Horario: '+ element.horario+ ' hs</li><br>');
        });

        this.tooltipString = this.tooltipString.concat('</ul>')
     }
}