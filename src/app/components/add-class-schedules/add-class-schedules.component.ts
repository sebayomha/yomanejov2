import { Component, OnInit, ViewChildren, QueryList, ViewChild } from '@angular/core';
import { trigger,animate,transition,style } from '@angular/animations';
import { MatSelectionList } from '@angular/material/list';
import { CronogramaService } from 'src/app/services/cronograma/cronograma.service';
import { Response } from 'src/app/models/response';
import { MatSnackBar } from '@angular/material/snack-bar';
import { SnackbarComponent } from '../snackbar/snackbar/snackbar.component';
import { ActivatedRoute,Router } from '@angular/router';
import { SharedService } from 'src/app/services/sharedService/shared-service';
declare var $: any;

@Component({
  selector: 'app-add-class-schedules',
  templateUrl: './add-class-schedules.component.html',
  styleUrls: ['./add-class-schedules.component.css'],
  animations: [
    trigger('fadeIn', [
      transition(':enter', [
        style({ opacity: '0' }),
        animate('.5s ease-out', style({ opacity: '1' })),
      ]),
      transition(':leave', [
        style({ opacity: '1' }),
        animate('.5s ease-out', style({ opacity: '0' })),
      ])
    ]),
  ]
})
export class AddClassSchedulesComponent implements OnInit {

  @ViewChildren(MatSelectionList) matSelectionLists !: QueryList<MatSelectionList>;
  @ViewChild('customModal') customModal;

  data;
  step:number;
  idAlumno: number;
  idCronograma: number;
  cantSelectedClasses: number;
  durationInSeconds: number = 3;
  number_of_classes: number = 1;
  showSuccessBanner: boolean = false;
  dataToConfirm = [];
  selectedOption;
  fechaClase;
  showPage: boolean = false;

  constructor(private sharedService: SharedService ,private cronogramaService: CronogramaService, private _snackBar: MatSnackBar, private route: ActivatedRoute, private router: Router) { }

  ngOnInit() {
    this.step = 0;
    this.cantSelectedClasses = 0;
    this.route.params.subscribe(params => {
      this.idCronograma = params['idCronograma'];
    });
    this.idAlumno = this.sharedService.getData();
    this.cronogramaService.obtenerClasesDisponiblesParaAlumno(this.idAlumno).subscribe( (response: Response) => {
      if (response.code == 0) {
        this.showPage = true;
        this.data =  Object.values(response.data);
      } else {
        this._snackBar.openFromComponent(SnackbarComponent, {
          duration: this.durationInSeconds * 1100,
          data: response
        });
      }
    })
  }

  ngAfterViewInit() {
    $('[data-toggle="tooltip"]').tooltip();
  }

  showMore(option) {
    option.showMoreHours = 20;
  }

  showLess(option) {
    option.showMoreHours = 4;
  }

  onCustomModalClose($event) {
    this.customModal.onClose();
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

  totalClassesSelected(indexSelected, event, fecha){
    this.matSelectionLists.forEach( (element, index) => {
      if (index != indexSelected)
        element.deselectAll();
    })
    if (event.option.selected) {
      event.source.deselectAll();
      this.selectedOption = event.option;
      this.fechaClase = fecha;
      event.option._setSelected(true);
      this.cantSelectedClasses = 1;
    } else {
      this.selectedOption = null;
      this.fechaClase = null;
      event.source.deselectAll();
      this.cantSelectedClasses = 0;
    }
  }

  saveOptions() {
    this.dataToConfirm = [];
    this.dataToConfirm.push({'idCronograma' : this.idCronograma});
    this.dataToConfirm.push({'selectedOption' : this.selectedOption.value});
    this.dataToConfirm.push({'idAlumno' : this.idAlumno});
    this.dataToConfirm.push({'fechaClase' : this.fechaClase});
    this.customModal.open();
  }

  volver() {
    this.router.navigate(['/pendientes']);
  }

  confirmSchedule(event) {
    this.cronogramaService.agregarClaseACronograma(event).subscribe( (response: Response) => {
      this.customModal.onClose();
      this._snackBar.openFromComponent(SnackbarComponent, {
        duration: this.durationInSeconds * 1100,
        data: response
      }).afterDismissed().subscribe( (afterDismiss) => {
        this.router.navigate(['/pendientes']);
      });
    })
  }
}
