import { Component, Input } from '@angular/core';

@Component({
  selector: 'available-schedules',
  templateUrl: './available-schedules.component.html',
  styleUrls: ['./available-schedules.component.scss', '../../global-styles.scss']
})

export class AvailableSchedulesComponent {


    @Input() data: any;

    step:number;
    order_information:any;

    constructor() { }

    ngOnInit() {
        this.step = 0;
        console.log('LLEGO', this.data);
        // if (this.data) this.orderInformation();
        
    }

    // orderInformation() {
    //     for (let i = 0; i <=6; i++) {
    //         let nombre_dia = this.getDay(i);
    //         this.order_information.push({'dia' : nombre_dia, 'opciones' : []});
    //         this.data.forEach(element => {
    //             if (element.dia == nombre_dia) {
    //                 this.order_information[i].opciones.push({'fecha':element.fecha,'autos':element.autos});
    //             } 
    //         });
    //     }
    //     console.log('dataordenada',this.order_information);
    // }
    
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

    setStep(index: number) {
        this.step = index;
    }

    nextStep() {
        this.step++;
    }

    prevStep() {
        this.step--;
    }

}