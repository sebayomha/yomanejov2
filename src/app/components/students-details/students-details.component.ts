import { Component, OnInit, ViewChild, Input, Output, EventEmitter } from '@angular/core';
import { MatSidenav } from '@angular/material/sidenav';

@Component({
  selector: 'app-students-details',
  templateUrl: './students-details.component.html',
  styleUrls: ['./students-details.component.css']
})
export class StudentsDetailsComponent implements OnInit {
  @ViewChild('sidenav') sidenav: MatSidenav;
  @Input('alumno') alumno;
  @Output() closed = new EventEmitter<string>();

  constructor() {}

  ngOnInit() {}

  ngOnChanges() {}

  close() {
    this.alumno = null;
    this.sidenav.close();
    this.closed.emit('cerrado');
  }

  open() {
    ('SDASD');
    this.sidenav.open();
  }
}
