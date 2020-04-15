import {
    transition,
    trigger,
    query,
    style,
    animate,
    group,
    animateChild,
    state,
    keyframes
 } from '@angular/animations';

export const slideInAnimation =
  trigger('routeAnimations', [
    transition('StudentsPage <=> EditingStudentPage', [
      style({ position: 'relative' }),
      query(':enter, :leave', [
        style({
          position: 'absolute',
          top: 0,
          left: 0,
          width: '100%'
        })
      ], { optional: true }),
      query(':enter', [
        style({ left: '-100%'})
      ], { optional: true }),
      query(':leave', animateChild(), { optional: true }),
      group([
        query(':leave', [
          animate('300ms ease-out', style({ left: '100%'}))
        ], { optional: true }),
        query(':enter', [
          animate('300ms ease-out', style({ left: '0%'}))
        ], { optional: true })
      ]),
      query(':enter', animateChild(), { optional: true }),
    ]),
    transition('* <=> EditingStudentPage', [
      style({ position: 'relative' }),
      query(':enter, :leave', [
        style({
          position: 'absolute',
          top: 0,
          left: 0,
          width: '100%'
        })
      ], { optional: true }),
      query(':enter', [
        style({ left: '-100%'})
      ], { optional: true }),
      query(':leave', animateChild(), { optional: true }),
      group([
        query(':leave', [
          animate('200ms ease-out', style({ left: '100%'}))
        ], { optional: true }),
        query(':enter', [
          animate('300ms ease-out', style({ left: '0%'}))
        ], { optional: true })
      ]),
      query(':enter', animateChild(), { optional: true }),
    ])
  ]);

export const flipAnimation =
  trigger('flipState', [
    state('active', style({
      transform: 'rotateY(179.9deg)'
    })),
    state('inactive', style({
      transform: 'rotateY(0)'
    })),
    transition('active => inactive', animate('500ms ease-out')),
    transition('inactive => active', animate('500ms ease-in'))
  ])
