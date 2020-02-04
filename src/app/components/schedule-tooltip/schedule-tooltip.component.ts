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
    items
    tooltipString = '';
    constructor() { }

    ngOnInit() {
        console.log('CRONOG',this.info_schedule_car);

        this.items = ['1','2'];

        this.tooltipString = '<br><ul style="margin-bottom: 0px;">';
        this.info_schedule_car.forEach(element => {
            this.tooltipString = this.tooltipString.concat('<li>Alumno: ' + element.alumno +'<br>Dire: '+ element.direccion +'<br> Horario: '+ element.horario+ ' hs</li><br>');
        });

        this.tooltipString = this.tooltipString.concat('</ul>')

        console.log(this.tooltipString)
     }

     

}